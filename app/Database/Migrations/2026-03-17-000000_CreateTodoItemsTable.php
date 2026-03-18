<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTodoItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'user_uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['todo', 'complete'],
                'default'    => 'todo',
            ],
            'markdown' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'html' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey('user_uuid');
        $this->forge->createTable('todo_items');
    }

    public function down()
    {
        $this->forge->dropTable('todo_items');
    }
}
