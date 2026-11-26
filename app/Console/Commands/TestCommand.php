<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
class TestCommand extends Command
{
    protected $signature = 'app:test-command';
    protected $description = 'Command description';
    public function handle() { return 0; }
}
