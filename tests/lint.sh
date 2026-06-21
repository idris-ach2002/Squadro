#!/usr/bin/env sh
set -eu
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
php tests/smoke.php
