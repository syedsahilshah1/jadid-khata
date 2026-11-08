const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const { spawn } = require('child_process');
const net = require('net');
const fs = require('fs');

process.on('uncaughtException', (err) => {
  log(`Uncaught Exception: ${err ? (err.stack || err.message) : err}`);
});
process.on('unhandledRejection', (reason) => {
  log(`Unhandled Rejection: ${reason}`);
});

delete process.env.ELECTRON_RUN_AS_NODE;

const LOG_FILE = path.join(__dirname, 'electron.log');
function log(msg) {
  const line = `[${new Date().toISOString()}] ${msg}\n`;
  try {
    fs.appendFileSync(LOG_FILE, line);
  } catch (e) {}
  console.log(msg);
}

let mainWindow = null;
let phpProcess = null;
let mysqlProcess = null;
let syncInterval = null;

const PORT = 8080;
const MYSQL_PORT = 3306;

// Resolve project root for both dev mode and packaged desktop app
function resolveProjectDir() {
  if (app && app.isPackaged && process.resourcesPath) {
    const unpackedApp = path.join(process.resourcesPath, 'app');
    if (fs.existsSync(unpackedApp)) return unpackedApp;
    const asarUnpacked = path.join(process.resourcesPath, 'app.asar.unpacked');
    if (fs.existsSync(asarUnpacked)) return asarUnpacked;
  }
  let resolved = path.resolve(__dirname, '..');
  if (resolved.includes('app.asar')) {
    const candidateUnpacked = resolved.replace('app.asar', 'app.asar.unpacked');
    if (fs.existsSync(candidateUnpacked)) return candidateUnpacked;
    const candidateApp = resolved.replace('app.asar', 'app');
    if (fs.existsSync(candidateApp)) return candidateApp;
  }
  return resolved;
}
const PROJECT_DIR = resolveProjectDir();

function isPortOpen(port, host = '127.0.0.1') {
  return new Promise((resolve) => {
    const socket = new net.Socket();
    socket.setTimeout(800);
    socket.on('connect', () => {
      socket.destroy();
      resolve(true);
    });
    socket.on('timeout', () => {
      socket.destroy();
      resolve(false);
    });
    socket.on('error', () => {
      resolve(false);
    });
    socket.connect(port, host);
  });
}

function getPhpPath() {
  const candidates = [];
  if (app && app.isPackaged && process.resourcesPath) {
    candidates.push(path.join(process.resourcesPath, 'bin', 'php', 'php.exe'));
    candidates.push(path.join(process.resourcesPath, 'app', 'bin', 'php', 'php.exe'));
    candidates.push(path.join(process.resourcesPath, 'app.asar.unpacked', 'bin', 'php', 'php.exe'));
  }
  candidates.push(path.join(PROJECT_DIR, 'bin', 'php', 'php.exe'));
  candidates.push(path.join(PROJECT_DIR.replace('app.asar', 'app'), 'bin', 'php', 'php.exe'));
  candidates.push(path.join(PROJECT_DIR.replace('app.asar', 'app.asar.unpacked'), 'bin', 'php', 'php.exe'));
  candidates.push('C:\\xampp\\php\\php.exe');

  for (const c of candidates) {
    if (!c.includes('app.asar') && fs.existsSync(c)) {
      return c;
    }
  }
  return 'php';
}

function getMysqlPath() {
  const candidates = [];
  if (app && app.isPackaged && process.resourcesPath) {
    candidates.push(path.join(process.resourcesPath, 'bin', 'mysql', 'bin', 'mysqld.exe'));
    candidates.push(path.join(process.resourcesPath, 'app', 'bin', 'mysql', 'bin', 'mysqld.exe'));
    candidates.push(path.join(process.resourcesPath, 'app.asar.unpacked', 'bin', 'mysql', 'bin', 'mysqld.exe'));
  }
  candidates.push(path.join(PROJECT_DIR, 'bin', 'mysql', 'bin', 'mysqld.exe'));
  candidates.push(path.join(PROJECT_DIR.replace('app.asar', 'app'), 'bin', 'mysql', 'bin', 'mysqld.exe'));
  candidates.push(path.join(PROJECT_DIR.replace('app.asar', 'app.asar.unpacked'), 'bin', 'mysql', 'bin', 'mysqld.exe'));
  candidates.push('C:\\xampp\\mysql\\bin\\mysqld.exe');

  for (const c of candidates) {
    if (!c.includes('app.asar') && fs.existsSync(c)) {
      return c;
    }
  }
  return null;
}

async function ensureDatabaseRunning() {
  const running = await isPortOpen(MYSQL_PORT);
  if (running) {
    log(`Database port ${MYSQL_PORT} already active.`);
    return true;
  }

  const mysqldPath = getMysqlPath();
  if (!mysqldPath) {
    log('mysqld.exe not found, skipping DB spawn.');
    return false;
  }

  log(`Starting MySQL from: ${mysqldPath}`);
  const mysqlBaseDir = path.resolve(path.dirname(mysqldPath), '..');
  const mysqlDataDir = path.join(mysqlBaseDir, 'data');
  const myIni = path.join(path.dirname(mysqldPath), 'my.ini');

  const args = [
    `--basedir=${mysqlBaseDir}`,
    `--datadir=${mysqlDataDir}`,
    '--standalone'
  ];
  if (fs.existsSync(myIni)) {
    args.unshift(`--defaults-file=${myIni}`);
  }

  try {
    mysqlProcess = spawn(mysqldPath, args, { detached: false, stdio: 'ignore' });
    mysqlProcess.on('error', (err) => {
      log(`MySQL process error: ${err.message}`);
    });
  } catch (err) {
    log(`MySQL spawn exception: ${err.message}`);
    return false;
  }

  for (let i = 0; i < 40; i++) {
    await new Promise((r) => setTimeout(r, 500));
    if (await isPortOpen(MYSQL_PORT)) {
      log('MySQL is now ready.');
      return true;
    }
  }

  log('Timeout waiting for MySQL to respond.');
  return false;
}

async function ensureBackendRunning() {
  const running = await isPortOpen(PORT);
  if (running) {
    log(`Backend port ${PORT} already active.`);
    return true;
  }

  const phpPath = getPhpPath();
  log(`Starting PHP server from: ${phpPath}`);

  try {
    phpProcess = spawn(phpPath, ['artisan', 'serve', `--port=${PORT}`], {
      cwd: PROJECT_DIR,
      detached: false,
      stdio: 'ignore'
    });
    phpProcess.on('error', (err) => {
      log(`PHP server error: ${err.message}`);
    });
  } catch (err) {
    log(`PHP spawn exception: ${err.message}`);
    return false;
  }

  for (let i = 0; i < 40; i++) {
    await new Promise((r) => setTimeout(r, 500));
    if (await isPortOpen(PORT)) {
      log('PHP server is now ready.');
      return true;
    }
  }

  log('Failed to start PHP server.');
  return false;
}

// Background Sync Trigger
function runBackgroundSync() {
  const phpPath = getPhpPath();
  log('Triggering background pos:sync...');
  const child = spawn(phpPath, ['artisan', 'pos:sync'], {
    cwd: PROJECT_DIR,
    detached: false,
    stdio: 'ignore'
  });
  child.on('error', (err) => {
    log(`pos:sync error: ${err.message}`);
  });
  child.on('exit', (code) => {
    log(`pos:sync exited with code ${code}`);
  });
}

async function createWindow() {
  log('Creating main BrowserWindow...');
  mainWindow = new BrowserWindow({
    width: 1440,
    height: 900,
    minWidth: 1024,
    minHeight: 700,
    title: 'Jadid Khata - Offline POS',
    autoHideMenuBar: true,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      preload: path.join(__dirname, 'preload.cjs')
    }
  });

  mainWindow.maximize();

  // Clear any stale Service Workers or cache storage from previous sessions
  try {
    await mainWindow.webContents.session.clearStorageData({
      storages: ['serviceworkers', 'cachestorage']
    });
  } catch (e) {
    log(`Session clear error: ${e.message}`);
  }

  // Open DevTools in separate window so errors can be inspected
  mainWindow.webContents.openDevTools({ mode: 'detach' });

  const targetUrl = `http://127.0.0.1:${PORT}/login`;
  log(`Loading POS URL: ${targetUrl}`);
  mainWindow.loadURL(targetUrl);

  mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription) => {
    log(`Page failed to load (${errorCode}: ${errorDescription}). Retrying in 2 seconds...`);
    setTimeout(() => {
      if (mainWindow) mainWindow.loadURL(targetUrl);
    }, 2000);
  });

  mainWindow.on('closed', () => {
    log('Main window closed');
    mainWindow = null;
  });
}

try {
  ipcMain.handle('print-receipt', async (event, options = {}) => {
    if (!mainWindow) return { success: false, error: 'No active window' };
    return new Promise((resolve) => {
      mainWindow.webContents.print(
        {
          silent: options.silent ?? true,
          printBackground: true,
          deviceName: options.deviceName || ''
        },
        (success, errorType) => {
          resolve({ success: !!success, error: errorType });
        }
      );
    });
  });

  ipcMain.handle('trigger-sync', async () => {
    runBackgroundSync();
    return { success: true };
  });
} catch (e) {
  log(`ipcMain error: ${e.message}`);
}

app.whenReady().then(async () => {
  log('app.whenReady fired');
  try {
    await ensureDatabaseRunning();
    await ensureBackendRunning();
    await createWindow();

    // Run auto-sync every 2 minutes
    syncInterval = setInterval(runBackgroundSync, 2 * 60 * 1000);
  } catch (err) {
    log(`Startup error: ${err.message}`);
  }

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) createWindow();
  });
});

function cleanupProcesses() {
  log('Cleaning up processes...');
  if (syncInterval) clearInterval(syncInterval);
  if (phpProcess) {
    try {
      process.kill(phpProcess.pid);
    } catch (e) {}
    phpProcess = null;
  }
  if (mysqlProcess) {
    try {
      process.kill(mysqlProcess.pid);
    } catch (e) {}
    mysqlProcess = null;
  }
}

app.on('before-quit', cleanupProcesses);
app.on('window-all-closed', () => {
  cleanupProcesses();
  if (process.platform !== 'darwin') {
    app.quit();
  }
});
