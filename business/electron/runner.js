const { spawn } = require('child_process');
const path = require('path');

const env = { ...process.env };
delete env.ELECTRON_RUN_AS_NODE;

const electronBinary = require('electron');
const appDir = path.resolve(__dirname, '..');

const child = spawn(electronBinary, [appDir, ...process.argv.slice(2)], {
  cwd: appDir,
  env,
  stdio: 'inherit',
  windowsHide: false
});

child.on('error', (err) => {
  console.error('Spawn error:', err);
});

child.on('close', (code, signal) => {
  console.log(`Electron closed with code ${code}, signal ${signal}`);
  process.exit(code ?? 0);
});
