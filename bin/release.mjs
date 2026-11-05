// Bumps the plugin version everywhere it is declared.
// Usage: npm run release 0.5.1
// Changelog entries and `@since` tags are left alone: they record history, not the current version.
import { readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../', import.meta.url));
const version = process.argv[2];

if (!version || !/^\d+\.\d+\.\d+(-[0-9A-Za-z.-]+)?$/.test(version)) {
  console.error('Usage: npm run release <version>   e.g. npm run release 0.5.1');
  process.exit(1);
}

// Each target: file + regex whose first group is kept and whose version part is replaced.
const targets = [
  { file: 'a-ripple-song.php', pattern: /^( \* Version:\s+)\S+/m },
  { file: 'README.txt', pattern: /^(Stable tag:\s*)\S+/m },
  { file: 'app/App.php', pattern: /(public const VERSION = ')[^']+/ },
  { file: 'resources/lang/a-ripple-song.pot', pattern: /("Project-Id-Version: A Ripple Song )[^\\"]+/ },
  { file: 'resources/lang/a-ripple-song-en_US.po', pattern: /("Project-Id-Version: A Ripple Song )[^\\"]+/ },
  { file: 'resources/lang/a-ripple-song-zh_CN.po', pattern: /("Project-Id-Version: A Ripple Song )[^\\"]+/ },
];

// Read and check everything first, so a missing pattern aborts before any file is written.
const updates = targets.map(({ file, pattern }) => {
  const path = join(root, file);
  const content = readFileSync(path, 'utf8');
  const match = content.match(pattern);
  if (!match) {
    console.error(`Version pattern not found in ${file}, nothing was changed.`);
    process.exit(1);
  }
  const old = match[0].slice(match[1].length);
  return { file, path, old, content: content.replace(pattern, `$1${version}`) };
});

for (const { file, path, old, content } of updates) {
  writeFileSync(path, content);
  console.log(`${file}: ${old} -> ${version}`);
}

// Sets `version` on a JSON object, placing it right after `name` when it is new (npm's key order).
const withVersion = (object) => {
  if ('version' in object) return { ...object, version };
  const result = {};
  for (const [key, value] of Object.entries(object)) {
    result[key] = value;
    if (key === 'name') result.version = version;
  }
  return result;
};

// package.json / package-lock.json keep the same version for npm tooling.
for (const file of ['package.json', 'package-lock.json']) {
  const path = join(root, file);
  let json = JSON.parse(readFileSync(path, 'utf8'));
  const old = json.version ?? '(none)';
  json = withVersion(json);
  if (json.packages?.['']) json.packages[''] = withVersion(json.packages['']);
  writeFileSync(path, `${JSON.stringify(json, null, 2)}\n`);
  console.log(`${file}: ${old} -> ${version}`);
}

console.log(`\nVersion set to ${version}. Remember to add a "= ${version} =" entry to the README.txt changelog.`);
