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
        $categories = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/categories.sql'));
        $advs = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/advs.sql'));
        $countries = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/countries.sql'));
        $cities = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/cities.sql'));
        $districts = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/districs.sql'));
        $neighborhoods = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/neighborhoods.sql'));
        $villages = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/village.sql'));
        $villages_trans = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/village_translations.sql'));
        Model::unguard();
        DB::unprepared($advs);
        DB::unprepared($categories);
        DB::unprepared($countries);
        DB::unprepared($cities);
        DB::unprepared($districts);
        DB::unprepared($neighborhoods);
        DB::unprepared($villages);
        DB::unprepared($villages_trans);
        Model::reguard();
        $zip = new \ZipArchive();
        $zip->open(realpath(dirname(__DIR__)) . '/src/Seed/Data/images.zip', ZipArchive::CREATE);
        $zip->extractTo(storage_path('streams/' . $application_reference . '/files-module/local/images/'));
        $zip->open(realpath(dirname(__DIR__)) . '/src/Seed/Data/cats.zip', ZipArchive::CREATE);
        $zip->extractTo(storage_path('streams/' . $application_reference . '/files-module/local/category_icon/'));
        $zip->close();
        //Sync Files
        $this->command->call('files:sync');
        Artisan::call('assets:clear');
    }
}
