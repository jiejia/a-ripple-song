// Copies the Lucide icons the PHP templates use into resources/assets/icons as one-line SVGs.
// Icons are found by scanning resources/templates for Icon::render('name' / Icon::svg('name'.
// Usage: npm run build:icons
import { readFileSync, writeFileSync, mkdirSync, readdirSync, rmSync } from 'node:fs';
import { createRequire } from 'node:module';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const require = createRequire(import.meta.url);
const source = join(dirname(require.resolve('lucide-static/package.json')), 'icons');
const templates = fileURLToPath(new URL('../resources/templates/', import.meta.url));
const target = fileURLToPath(new URL('../resources/assets/icons/', import.meta.url));

const icons = new Set();
for (const file of readdirSync(templates, { recursive: true })) {
  if (!String(file).endsWith('.php')) continue;
  for (const [, name] of readFileSync(join(templates, String(file)), 'utf8').matchAll(/Icon::(?:render|svg)\('([\w-]+)'/g)) icons.add(name);
}

rmSync(target, { recursive: true, force: true });
mkdirSync(target, { recursive: true });

for (const name of [...icons].sort()) {
  const svg = readFileSync(join(source, `${name}.svg`), 'utf8')
    .replace(/<!--[\s\S]*?-->/g, '')
    .replace(/\s*class="[^"]*"/, '')
    // The prototypes render Lucide with a 1.75 stroke.
    .replace('stroke-width="2"', 'stroke-width="1.75"')
    .replace(/\s+/g, ' ')
    .replace(/> </g, '><')
    .trim();

  writeFileSync(join(target, `${name}.svg`), `${svg}\n`);
}

console.log(`Copied ${icons.size} Lucide icons (ISC license) to resources/assets/icons/`);
