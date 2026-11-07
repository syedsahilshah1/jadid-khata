const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('posDesktop', {
  isDesktop: true,
  printReceipt: (options) => ipcRenderer.invoke('print-receipt', options)
});

