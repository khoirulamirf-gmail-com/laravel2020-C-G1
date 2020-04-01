<?php

use Illuminate\Database\Seeder;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $administrator = new \App\User;
        $administrator->username = 'admin';
        $administrator->name = 'admin';
        $administrator->email =  'admin@gmail.com';
        $administrator->roles = json_encode(["ADMIN"]);
        $administrator->password = bcrypt('password');
        $administrator->avatar = Str::random(10).".png";
        $administrator->address = 'pemalang';
        $administrator->phone = '085742777';
        $administrator->save();
        $this->command->info("User Admin berhasil diinsert");
    }
}
