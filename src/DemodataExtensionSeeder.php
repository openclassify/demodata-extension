<?php
namespace Visiosoft\DemodataExtension;

use \Anomaly\Streams\Platform\Database\Seeder\Seeder;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\ArgvInput;
use ZipArchive;
use Anomaly\UsersModule\User\Contract\UserRepositoryInterface;

class DemodataExtensionSeeder extends Seeder
{
    private $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    public function run()
    {
        $faker = Factory::create();

        for ($i=0; $i < 10; $i++) {
            $id = 77770 + $i;
            if (!$this->userRepository->findBy("id",$id)){
                $user = [
                    'id' => $id,
                    'username' => $faker->userName,
                    'first_name' => $faker->firstName,
                    'last_name' => $faker->lastName,
                    'display_name' => $faker->name,
                    'email' => $faker->safeEmail,
                    'password' => 'openclassify',
                    'activated' => 1,
                    'enabled' => 1,
                    'gsm_phone' => $faker->e164PhoneNumber,
                    'land_phone' => $faker->e164PhoneNumber,
                    'office_phone' => $faker->e164PhoneNumber,
                ];
            $this->userRepository->create($user);
        }
    }

        $application_reference = (new ArgvInput())->getParameterOption('--app', env('APPLICATION_REFERENCE', 'default'));
        $categories = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/categories.sql'));
        $categoriesTrans = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/categoryTransEn.sql'));
        $advs = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/advs.sql'));
        Model::unguard();
        DB::unprepared($advs);
        DB::unprepared($categories);
        DB::unprepared($categoriesTrans);
        if (is_module_installed('visiosoft.module.customfields')){
            $customfields = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/customfields.sql'));
            DB::unprepared($customfields);
        }
        if (is_module_installed('visiosoft.module.store')) {
            $store = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/store.sql'));
            DB::unprepared($store);
        }
        if (is_module_installed('visiosoft.module.dopings')) {
            $dopings = str_replace('{application_reference}', $application_reference, file_get_contents(realpath(dirname(__DIR__)) . '/src/Seed/Data/dopings.sql'));
            DB::unprepared($dopings);
        }
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
