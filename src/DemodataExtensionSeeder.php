<?php
namespace Visiosoft\DemodataExtension;

use \Anomaly\Streams\Platform\Database\Seeder\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\ArgvInput;
use ZipArchive;

class DemodataExtensionSeeder extends Seeder
{
    public function run()
    {
        $application_reference = (new ArgvInput())->getParameterOption('--app', env('APPLICATION_REFERENCE', 'default'));
        $categories = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/seed/data/categories.sql'));
        $advs = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/seed/data/advs.sql'));
        Model::unguard();
        DB::unprepared($advs);
        DB::unprepared($categories);
        Model::reguard();
        $zip = new \ZipArchive();
        $zip->open(realpath(dirname(__DIR__)) . '/src/seed/data/images.zip', ZipArchive::CREATE);
        $zip->extractTo(storage_path('streams/' . $application_reference . '/files-module/local/images/'));
        $zip->open(realpath(dirname(__DIR__)) . '/src/seed/data/cats.zip', ZipArchive::CREATE);
        $zip->extractTo(storage_path('streams/' . $application_reference . '/files-module/local/category_icon/'));
        $zip->close();
        //Sync Files
        $this->command->call('files:sync');
        Artisan::call('assets:clear');
    }
}