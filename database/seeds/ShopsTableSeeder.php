<?php

use App\Shop;
use Illuminate\Database\Seeder;

class ShopsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Live Data */
        /* Shop::updateOrCreate(
            ['id' => 1],
            [
                'shopname_id'       => 1,
                'company_id'        => 1,
                'mail_driver'       => 'smtp',
                'mail_host'         => 'smtp.office365.com',
                'mail_port'         => 587,
                'mail_from_address' => 'rakuten@OSTshop.onmicrosoft.com',
                'mail_from_name'    => 'Rakuten Service - O.s.t. Ocean Sun Templin Ug',
                'mail_username'     => 'rakuten@OSTshop.onmicrosoft.com',
                'mail_password'     => random_int(111111, 999999) . '%_%!Templin8520!%_%' . uniqid(),
                'mail_encryption'   => 'tls',
                'customer_number'   => '958803',
                'password'          => random_int(111111, 999999) . '%_%R@kuten123%_%' . uniqid(),
                'api_key'           => 'd6872f58e1ac5c8af686562c6e882ba4',
                'active'            => 'yes',
            ]
        );

        Shop::updateOrCreate(
            ['id' => 2],
            [
                'shopname_id'       => 1,
                'company_id'        => 2,
                'mail_driver'       => 'smtp',
                'mail_host'         => 'smtp.office365.com',
                'mail_port'         => 587,
                'mail_from_address' => 'rakuten@MicrosoftOSTshop.onmicrosoft.com',
                'mail_from_name'    => 'Rakuten Service - O.s.t. Multishop',
                'mail_username'     => 'rakuten@MicrosoftOSTshop.onmicrosoft.com',
                'mail_password'     => random_int(111111, 999999) . '%_%!Templin8520!%_%' . uniqid(),
                'mail_encryption'   => 'tls',
                'customer_number'   => '418593',
                'password'          => random_int(111111, 999999) . '%_%GCXt8L123&%_%' . uniqid(),
                'api_key'           => '02d9a065222621ec6d32164203de2c0b',
                'active'            => 'yes',
            ]
        );

        Shop::updateOrCreate(
            ['id' => 3],
            [
                'shopname_id'       => 1,
                'company_id'        => 3,
                'mail_driver'       => 'smtp',
                'mail_host'         => 'smtp.strato.de',
                'mail_port'         => 465,
                'mail_from_address' => 'service.rakuten@benjamin-shop.de',
                'mail_from_name'    => 'Rakuten Service - O.s.t. Multishop',
                'mail_username'     => 'service.rakuten@benjamin-shop.de',
                'mail_password'     => random_int(111111, 999999) . '%_%www.HERM.de%_%' . uniqid(),
                'mail_encryption'   => 'ssl',
                'customer_number'   => '667474',
                'password'          => random_int(111111, 999999) . '%_%GCXt8L123&%_%' . uniqid(),
                'api_key'           => '6e1e0f81e1ab900fce7e8cc89f01706e',
                'active'            => 'yes',
            ]
        ); */

        /* Testing Data */
        Shop::updateOrCreate(
            ['id' => 1],
            [
                'shopname_id'       => 1,
                'company_id'        => 1,
                'mail_driver'       => 'smtp',
                'mail_host'         => 'smtp.mailtrap.io',
                'mail_port'         => 2525,
                'mail_from_address' => 'rakuten@OSTshop.onmicrosoft.com',
                'mail_from_name'    => 'Rakuten Service - O.s.t. Ocean Sun Templin Ug',
                'mail_username'     => '9a14e0eed0a84b',
                'mail_password'     => random_int(111111, 999999) . '%_%639be9ed9c5112%_%' . uniqid(),
                'mail_encryption'   => 'tls',
                'customer_number'   => '958803',
                'password'          => random_int(111111, 999999) . '%_%R@kuten123%_%' . uniqid(),
                'api_key'           => 'd6872f58e1ac5c8af686562c6e882ba4',
                'active'            => 'yes',
            ]
        );

        Shop::updateOrCreate(
            ['id' => 2],
            [
                'shopname_id'       => 1,
                'company_id'        => 2,
                'mail_driver'       => 'smtp',
                'mail_host'         => 'smtp.mailtrap.io',
                'mail_port'         => 2525,
                'mail_from_address' => 'rakuten@MicrosoftOSTshop.onmicrosoft.com',
                'mail_from_name'    => 'Rakuten Service - O.s.t. Multishop',
                'mail_username'     => 'fa775c0083665c',
                'mail_password'     => random_int(111111, 999999) . '%_%8e36bb690d9f25%_%' . uniqid(),
                'mail_encryption'   => 'tls',
                'customer_number'   => '418593',
                'password'          => random_int(111111, 999999) . '%_%GCXt8L123&%_%' . uniqid(),
                'api_key'           => '02d9a065222621ec6d32164203de2c0b',
                'active'            => 'yes',
            ]
        );

        Shop::updateOrCreate(
            ['id' => 3],
            [
                'shopname_id'       => 1,
                'company_id'        => 3,
                'mail_driver'       => 'smtp',
                'mail_host'         => 'smtp.mailtrap.io',
                'mail_port'         => 2525,
                'mail_from_address' => 'service.rakuten@benjamin-shop.de',
                'mail_from_name'    => 'Rakuten Service - O.s.t. Multishop',
                'mail_username'     => '80a5888240cf4b',
                'mail_password'     => random_int(111111, 999999) . '%_%e44ceee46ee889%_%' . uniqid(),
                'mail_encryption'   => 'tls',
                'customer_number'   => '667474',
                'password'          => random_int(111111, 999999) . '%_%GCXt8L123&%_%' . uniqid(),
                'api_key'           => '6e1e0f81e1ab900fce7e8cc89f01706e',
                'active'            => 'yes',
            ]
        );
    }
}

