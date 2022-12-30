<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::insert("
        INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `created_at`, `updated_at`) VALUES
        (1, 'App\\\Models\\\User', 1, 'portal', '5d1d853bb6b09cbc87b0bae6190bba270f39f8b6064b49c014c99aa04e156848', '[\"*\"]', '2022-12-30 15:51:34', '2022-12-30 15:49:13', '2022-12-30 15:51:34'),
        (2, 'App\\\Models\\\User', 2, 'portal', '9dcb72488dd6c7a7e3dd1db4abf374c2dfa7d364f3bc537aea1b6986722f9f2d', '[\"*\"]', NULL, '2022-12-30 15:50:35', '2022-12-30 15:50:35'),
        (3, 'App\\\Models\\\User', 3, 'portal', 'd4a193aac9c89de3dd52bc32a418d80efc96f19eb5e69d58612465977d9e02ee', '[\"*\"]', NULL, '2022-12-30 15:51:04', '2022-12-30 15:51:04');
                ");
    }
}
