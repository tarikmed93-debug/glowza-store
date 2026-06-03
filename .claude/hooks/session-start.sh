#!/bin/bash
# SessionStart hook: wire universal agent skills (.agents/skills) into
# .claude/skills so Claude Code auto-discovers all of them every session.
set -euo pipefail

PROJECT_DIR="${CLAUDE_PROJECT_DIR:-$(git rev-parse --show-toplevel 2>/dev/null || pwd)}"
SRC="$PROJECT_DIR/.agents/skills"
DEST="$PROJECT_DIR/.claude/skills"

[ -d "$SRC" ] || exit 0
mkdir -p "$DEST"

linked=0
for skill_dir in "$SRC"/*/; do
  [ -d "$skill_dir" ] || continue
  name="$(basename "$skill_dir")"
  link="$DEST/$name"
  if [ ! -e "$link" ] && [ ! -L "$link" ]; then
    ln -s "../../.agents/skills/$name" "$link"
    linked=$((linked + 1))
  fi
done

echo "session-start: $(ls -1 "$DEST" | wc -l | tr -d ' ') skills wired into .claude/skills ($linked new)" >&2
