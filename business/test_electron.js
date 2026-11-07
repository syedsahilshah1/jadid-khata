const e = require('electron');
console.log('Type of electron:', typeof e);
if (typeof e === 'object') {
  console.log('Keys:', Object.keys(e));
} else {
  console.log('Value:', e);
}

