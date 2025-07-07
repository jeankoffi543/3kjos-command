<?php

namespace Kjos\Command\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupPublishedFilesCommand extends Command
{
    protected $signature = 'kjos:vendor:cleanup';

    protected $description = 'Delete all published files';

    public function handle()
    {
        try {
         if(File::exists($path = config_path('3kjos-command.php'))) {
            File::delete($path);
         }
        } catch (\Exception $e) {
        }
    }
}
