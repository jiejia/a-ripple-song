#!/usr/bin/env bash
# Releases a new plugin version: sets the version everywhere, commits, tags and pushes.
# The tag push starts .github/workflows/release.yml, which builds and publishes a-ripple-song.zip.
#
# Usage: npm run release 0.5.1              (or: bash bin/release.sh v0.5.1)
#        npm run release 0.5.1 -- --no-push   (commit and tag locally only)
#
# Giving the version the plugin is already at re-releases it: the old tag is deleted (locally and
# on origin) and recreated on the current commit, so the workflow builds the zip again.
#
# Files updated: a-ripple-song.php (header Version), README.txt (Stable tag, plus a changelog entry
# when it has a == Changelog == section and the version is new),
# app/App.php, package.json, package-lock.json and the translation headers in resources/lang.
# `@since` tags and older changelog entries are history and stay as they are.
set -euo pipefail

cd "$(dirname "$0")/.."

push=1
version=""

for arg in "$@"; do
  case "$arg" in
    --no-push) push=0 ;;
    -*) echo "Unknown option: $arg" >&2; exit 1 ;;
    *) version="${arg#v}" ;;
  esac
done

fail() { echo "release: $*" >&2; exit 1; }

[[ -n "$version" ]] || fail "give a version, e.g. npm run release 0.5.1"
[[ "$version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]] || fail "version must look like 1.2.3, got $version"

tag="v$version"
current="$(sed -n 's/^ \* Version:[[:space:]]*//p' a-ripple-song.php | tr -d '[:space:]')"

retag=0
[[ "$version" == "$current" ]] && retag=1

[[ "$(git branch --show-current)" == "main" ]] || fail "switch to the main branch first"
# AGENTS.md is local notes, so it doesn't count as a pending change.
if ! git diff --quiet -- . ':!AGENTS.md' || ! git diff --cached --quiet -- . ':!AGENTS.md'; then
  fail "commit or stash your changes first"
fi
if [[ $retag -eq 0 ]]; then
  git rev-parse -q --verify "refs/tags/$tag" > /dev/null && fail "tag $tag already exists"
fi

if [[ $push -eq 1 ]]; then
  # --force: a re-release may have replaced the tag on origin, so let it update the local one.
  git fetch -q --force origin main --tags
  [[ -z "$(git rev-list HEAD..origin/main)" ]] || fail "main is behind origin/main, pull first"
  if [[ $retag -eq 0 ]]; then
    git ls-remote --exit-code --tags origin "refs/tags/$tag" > /dev/null && fail "tag $tag already exists on origin"
  fi
fi

if [[ $retag -eq 1 ]]; then
  echo "Re-releasing $version: $tag will be deleted and recreated on the current commit"
else
  echo "Releasing $current -> $version"
fi

# Changelog entry: the commit subjects since the last version tag. Skipped when README.txt has no
# == Changelog == section (release notes live on the website), when re-releasing the current
# version, or when the entry already exists.
# Looks only inside == Changelog ==, since == Upgrade Notice == uses the same "= 1.2.3 =" headings.
changelog_has() { awk -v h="= $1 =" '/^== /{s=($0=="== Changelog ==")} s&&$0==h{f=1} END{exit !f}' README.txt; }
has_changelog=0
grep -q "^== Changelog ==$" README.txt && has_changelog=1
if [[ $has_changelog -eq 1 && $retag -eq 0 ]] && ! changelog_has "$version"; then
  previous="$(git describe --tags --abbrev=0 --match 'v[0-9]*' 2> /dev/null || true)"
  changes="$(git log --no-merges --format='* %s' ${previous:+"$previous"..HEAD} | grep -v '^\* Release v' || true)"
  [[ -n "$changes" ]] || changes="* Maintenance release."
  CHANGES="$changes" VERSION="$version" perl -0pi -e 's/(== Changelog ==\n\n)/$1= $ENV{VERSION} =\n$ENV{CHANGES}\n\n/' README.txt
fi

sed -i.bak "s/^\( \* Version:[[:space:]]*\).*/\1$version/" a-ripple-song.php
sed -i.bak "s/^Stable tag: .*/Stable tag: $version/" README.txt
sed -i.bak "s/public const VERSION = '[^']*';/public const VERSION = '$version';/" app/App.php
sed -i.bak "s/\"Project-Id-Version: A Ripple Song [^\\\\]*\\\\n\"/\"Project-Id-Version: A Ripple Song $version\\\\n\"/" resources/lang/*.pot resources/lang/*.po
rm -f a-ripple-song.php.bak README.txt.bak app/App.php.bak resources/lang/*.bak

npm version "$version" --no-git-tag-version --allow-same-version > /dev/null

# Every place must now carry the new version.
grep -q "^ \* Version:[[:space:]]*$version$" a-ripple-song.php || fail "a-ripple-song.php was not updated"
grep -q "^Stable tag: $version$" README.txt || fail "README.txt was not updated"
if [[ $has_changelog -eq 1 && $retag -eq 0 ]]; then
  changelog_has "$version" || fail "README.txt changelog was not updated"
fi
grep -q "VERSION = '$version'" app/App.php || fail "app/App.php was not updated"
for file in resources/lang/*.pot resources/lang/*.po; do
  grep -q "Project-Id-Version: A Ripple Song $version" "$file" || fail "$file was not updated"
done

files=(a-ripple-song.php README.txt app/App.php package.json package-lock.json resources/lang)
git add -- "${files[@]}"
# A re-release usually has nothing to commit; then the tag goes on the current commit.
if ! git diff --cached --quiet -- "${files[@]}"; then
  git commit -q -m "Release $tag" -- "${files[@]}"
  echo "Committed Release $tag"
fi

if [[ $retag -eq 1 ]]; then
  git tag -d "$tag" > /dev/null 2>&1 && echo "Deleted local tag $tag" || true
  if [[ $push -eq 1 ]] && git ls-remote --exit-code --tags origin "refs/tags/$tag" > /dev/null; then
    git push -q origin ":refs/tags/$tag"
    echo "Deleted $tag on origin"
  fi
fi

git tag -a "$tag" -m "$tag"
echo "Tagged $tag"

if [[ $push -eq 1 ]]; then
  git push -q --atomic origin main "$tag"
  echo "Pushed main and $tag. GitHub Actions now builds and publishes a-ripple-song.zip."
else
  echo "Not pushed. When ready: git push --atomic origin main $tag"
fi
