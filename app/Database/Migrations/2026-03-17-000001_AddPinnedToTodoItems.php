<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPinnedToTodoItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('todo_items', [
            'is_pinned' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'category',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('todo_items', 'is_pinned');
    }
}
