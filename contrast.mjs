import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const content = fs.readFileSync(path.join(__dirname, 'tailwind.config.js'), 'utf8');

const colors = {};

const colorBlockRegex = /([a-z]+):\s*\{([\s\S]*?)\n\s*\},/g;
let blockMatch;
while ((blockMatch = colorBlockRegex.exec(content)) !== null) {
  const name = blockMatch[1];
  const body = blockMatch[2];
  const innerRegex = /([0-9]+):\s*['"]#([0-9a-fA-F]{6})['"]/g;
  let inner;
  while ((inner = innerRegex.exec(body)) !== null) {
    colors[name + '-' + inner[1]] = inner[2].toUpperCase();
  }
}

const simpleColorRegex = /([a-z]+):\s*['"]#([0-9a-fA-F]{6})['"]/g;
let simpleMatch;
while ((simpleMatch = simpleColorRegex.exec(content)) !== null) {
  const key = simpleMatch[1];
  if (key === 'papel' || key === 'marfil') {
    colors[key] = simpleMatch[2].toUpperCase();
  }
}

function hexToRgb(hex) {
  const r = parseInt(hex.substr(0,2), 16) / 255;
  const g = parseInt(hex.substr(2,2), 16) / 255;
  const b = parseInt(hex.substr(4,2), 16) / 255;
  return [r,g,b];
}

function luminance(hex) {
  let [r,g,b] = hexToRgb(hex);
  [r,g,b] = [r,g,b].map(c => {
    if (c <= 0.03928) return c / 12.92;
    return Math.pow((c + 0.055) / 1.055, 2.4);
  });
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

function contrast(c1, c2) {
  const l1 = luminance(c1);
  const l2 = luminance(c2);
  const L1 = Math.max(l1,l2);
  const L2 = Math.min(l1,l2);
  return (L1 + 0.05) / (L2 + 0.05);
}

const pairs = [
  ['oro-500','marfil'],['oro-500','papel'],['oro-500','blanco'],
  ['oro-600','marfil'],['oro-600','papel'],['oro-600','blanco'],
  ['oro-700','marfil'],['oro-700','papel'],['oro-700','blanco'],
  ['oro-800','marfil'],['oro-800','papel'],['oro-800','blanco'],
  ['oro-900','marfil'],['oro-900','papel'],['oro-900','blanco'],
  ['neutral-400','marfil'],['neutral-400','papel'],['neutral-400','blanco'],
  ['neutral-500','marfil'],['neutral-500','papel'],['neutral-500','blanco'],
  ['neutral-600','marfil'],['neutral-600','papel'],['neutral-600','blanco'],
  ['neutral-700','marfil'],['neutral-700','papel'],['neutral-700','blanco'],
  ['papel','verde-700'],['marfil','verde-700'],
  ['papel','verde-800'],['marfil','verde-800'],
  ['papel','verde-950'],['marfil','verde-950'],
  ['verde-700','marfil'],['verde-800','marfil'],['verde-950','marfil'],
  ['verde-950','oro-500'],['verde-800','oro-500'],
];

console.log('COLORES LEIDOS DESDE tailwind.config.js:');
console.log(JSON.stringify(colors, null, 2));
console.log('\nCONTRASTE:');
for (const [fg,bg] of pairs) {
  if (!colors[fg] || !colors[bg]) {
    console.log(fg + '/' + bg + ': SIN COLOR');
    continue;
  }
  const r = contrast(colors[fg], colors[bg]);
  const p45 = r >= 4.5 ? 'PASS' : 'FAIL';
  const p3 = r >= 3.0 ? 'PASS' : 'FAIL';
  console.log(fg + '/' + bg + ': ' + r.toFixed(2) + ':1 | normal(4.5)= ' + p45 + ' | grande(3.0)= ' + p3);
}
