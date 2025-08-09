<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FoxQL\FoxQL;

// Create a database connection
$db = new FoxQL([
    'type' => 'mysql',
    'host' => 'localhost',
    'database' => 'test_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'prefix' => ''
]);

// Example 1: Create a new table using the migration builder
echo "Creating users table...\n";
$result = $db->createTable('users', function($table) {
    $table->increments('id');
    $table->string('name', 100)->notNull();
    $table->string('email', 100)->notNull()->unique();
    $table->string('password', 255)->notNull();
    $table->boolean('active')->default(true);
    $table->timestamps(); // Adds created_at and updated_at columns
});

if ($result) {
    echo "Users table created successfully!\n";
} else {
    echo "Error creating users table: " . $db->getError() . "\n";
}

// Example 2: Create a table with foreign keys
echo "\nCreating posts table...\n";
$result = $db->createTable('posts', function($table) {
    $table->increments('id');
    $table->integer('user_id')->notNull();
    $table->string('title', 200)->notNull();
    $table->text('content');
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
    $table->softDeletes(); // Adds deleted_at column for soft deletes
    
    // Add foreign key constraint
    $table->foreign('user_id', 'users', 'id', null, 'CASCADE', 'CASCADE');
});

if ($result) {
    echo "Posts table created successfully!\n";
} else {
    echo "Error creating posts table: " . $db->getError() . "\n";
}

// Example 3: Alter an existing table
echo "\nAltering users table...\n";
$result = $db->alterTable('users', function($table) {
    $table->string('username', 50)->after('name');
    $table->string('phone', 20)->nullable();
});

if ($result) {
    echo "Users table altered successfully!\n";
} else {
    echo "Error altering users table: " . $db->getError() . "\n";
}

// Example 4: Rename a table
echo "\nRenaming posts table to articles...\n";
$result = $db->renameTable('posts', 'articles');

if ($result) {
    echo "Table renamed successfully!\n";
} else {
    echo "Error renaming table: " . $db->getError() . "\n";
}

// Example 5: Drop a table
echo "\nDropping articles table...\n";
$result = $db->dropTable('articles');

if ($result) {
    echo "Table dropped successfully!\n";
} else {
    echo "Error dropping table: " . $db->getError() . "\n";
}

// Example 6: Create a migration file
echo "\nCreating a sample migration file...\n";

$migrationContent = <<<'EOT'
<?php

namespace Migrations;

use FoxQL\Models\Migration;

class CreateCommentsTable
{
    /**
     * Run the migration.
     *
     * @param \FoxQL\Models\Migration $migration
     * @return void
     */
    public function up(Migration $migration): void
    {
        $migration->createTable('comments', function($table) {
            $table->increments('id');
            $table->integer('user_id')->notNull();
            $table->integer('article_id')->notNull();
            $table->text('content')->notNull();
            $table->timestamps();
            
            // Add foreign keys
            $table->foreign('user_id', 'users', 'id', null, 'CASCADE', 'CASCADE');
            $table->foreign('article_id', 'articles', 'id', null, 'CASCADE', 'CASCADE');
        });
    }
    
    /**
     * Reverse the migration.
     *
     * @param \FoxQL\Models\Migration $migration
     * @return void
     */
    public function down(Migration $migration): void
    {
        $migration->dropTable('comments');
    }
}
EOT;

// Create migrations directory if it doesn't exist
if (!is_dir(__DIR__ . '/migrations')) {
    mkdir(__DIR__ . '/migrations', 0755, true);
}

// Write the migration file
file_put_contents(__DIR__ . '/migrations/20230101000000_create_comments_table.php', $migrationContent);
echo "Migration file created successfully!\n";

// Example 7: Run migrations
echo "\nRunning migrations...\n";
$migrations = $db->migrate(__DIR__ . '/migrations');

if (!empty($migrations)) {
    echo "Executed migrations: " . implode(", ", $migrations) . "\n";
} else {
    echo "No migrations executed or error occurred: " . $db->getError() . "\n";
}

// Example 8: Rollback migrations
echo "\nRolling back migrations...\n";
$rolledBack = $db->rollbackMigrations(__DIR__ . '/migrations');

if (!empty($rolledBack)) {
    echo "Rolled back migrations: " . implode(", ", $rolledBack) . "\n";
} else {
    echo "No migrations rolled back or error occurred: " . $db->getError() . "\n";
}

// Example 9: Reset all migrations
echo "\nResetting all migrations...\n";
$reset = $db->reset(__DIR__ . '/migrations');

if (!empty($reset)) {
    echo "Reset migrations: " . implode(", ", $reset) . "\n";
} else {
    echo "No migrations reset or error occurred: " . $db->getError() . "\n";
}

// Example 10: Refresh all migrations (reset and re-run)
echo "\nRefreshing all migrations...\n";
$refresh = $db->refresh(__DIR__ . '/migrations');

if (!empty($refresh['executed'])) {
    echo "Refreshed migrations: " . implode(", ", $refresh['executed']) . "\n";
} else {
    echo "No migrations refreshed or error occurred: " . $db->getError() . "\n";
}