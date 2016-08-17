# API Authenticated Based On Packet Sent

The main motivation for creating this package is to have a lot more flexibility and security for API based communication. I have used JWT in the past and found it to be scarily easy to hack!

I have followed the principles outlined and implemented at Twitter.

## Quick Start

### Setup

Update your `composer.json` file and add the following under the `require` key

	"linkthrow/laravel-hmac-packet-auth": "dev-master"

Run the composer update command:

	$ composer update

In your `config/app.php` add `'LinkThrow\HmacPacketAuth\Provider\HmacPacketAuthServiceProvider'` to the end of the `$providers` array

    'providers' => array(

        'Illuminate\Foundation\Providers\ArtisanServiceProvider',
        'Illuminate\Auth\AuthServiceProvider',
        ...
        'LinkThrow\HmacPacketAuth\Provider\HmacPacketAuthServiceProvider',

    ),

Run the `artisan` command below to publish the configuration file

	$ php artisan vendor:publish

Navigate to `app/config/hmacPacketAuth.php` and update all parameters

Run the `migrate` command below to add the database tables required

	$ php artisan migrate

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Authors

* **Hussan Choudhry**

See also the list of [contributors](https://github.com/your/project/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details