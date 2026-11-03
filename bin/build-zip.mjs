// Packages the plugin for WordPress.org as ../a-ripple-song.zip (next to the plugin folder, outside the repo).
// It zips the php-scoper output in build/ (namespaces prefixed with ARippleSong\, Composer autoloader
// without dev packages), and only the runtime files below, so dev files stay out.
// Usage: npm run zip (builds the assets, then runs `composer scope`)
import { cpSync, existsSync, mkdtempSync, rmSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { tmpdir } from 'node:os';
import { basename, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../build/', import.meta.url));
const slug = 'a-ripple-song';
const files = ['a-ripple-song.php', 'README.txt', 'LICENSE', 'app', 'resources', 'public', 'vendor'];
const output = join(root, '..', '..', `${slug}.zip`);

if (!existsSync(join(root, 'public/dist/player.js'))) {
  throw new Error('build/public/dist is missing, run `npm run build && composer scope` first.');
}

const staging = mkdtempSync(join(tmpdir(), `${slug}-`));

try {
  for (const file of files) {
    // Skip hidden files such as .gitkeep, which WordPress.org rejects.
    cpSync(join(root, file), join(staging, slug, file), {
      recursive: true,
      filter: (source) => !basename(source).startsWith('.'),
    });
  }

  rmSync(output, { force: true });
  execFileSync('zip', ['-rqX', output, slug], { cwd: staging });
} finally {
  rmSync(staging, { recursive: true, force: true });
}

console.log(`Created ${output}`);
