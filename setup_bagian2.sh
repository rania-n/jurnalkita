#!/bin/bash
set -e

echo "Updating User.php..."
sed -i '' "s/'admin', 'guru', 'siswa'/'admin', 'guru', 'siswa', 'satpam'/g" app/Models/User.php
sed -i '' "s/return match (\$this->role) {/return match (\$this->role) {\n            'satpam' => 'satpam.dashboard',/" app/Models/User.php

# Wait, the enum for roleLabel
sed -i '' "s/'admin' => 'Admin',/'admin' => 'Admin',\n            'satpam' => 'Satpam',/" app/Models/User.php

echo "Done modifying User.php"

