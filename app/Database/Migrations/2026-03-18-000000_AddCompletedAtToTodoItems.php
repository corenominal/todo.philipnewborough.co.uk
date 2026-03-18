<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompletedAtToTodoItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('todo_items', [
            'completed_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'category',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('todo_items', 'completed_at');
    }
}
