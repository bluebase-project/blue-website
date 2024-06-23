### Blue Website

### About

Blue Website is a full-fledged website used together with the Blue Server.
It is created in PHP with a simple custom procedural framework.

### This is a fork from [Znote/ZnoteAAC](https://github.com/Znote/ZnoteAAC/) based on branch: [v2](https://github.com/Znote/ZnoteAAC/tree/v2)

### Download

We use github to distribute our versions, stable are tagged as releases, while development is the latest commit.
* [Stable](https://github.com/bluebase-project/blue-website/releases)
* [Development](https://github.com/bluebase-project/blue-website/archive/master.zip)

### Requirements
* PHP Version 5.6 or higher. Mostly tested on 5.6 and 7.4. Most web stacks ships with this as default these days.

### Optionals
* For email registration verification and account recovery: [PHPMailer](https://github.com/PHPMailer/PHPMailer/releases) Version 6.x, extracted and renamed to just "PHPMailer" in Znote AAC directory.
* PHP extension curl for PHPMailer, paypal and google reCaptcha services.
* PHP extension openssl for google reCaptcha services.

### Installation instructions

1: Extract the .zip file to your web directory (Example: C:\xampp\htdocs\ )
Without modifying config.php, enter the website and wait for mysql connection error.
This will show you the rest of the instructions as well as the mysql schema.

2: Edit config.php and:
- modify $config['page_admin_access'] with your admin account username(s).

3: Before inserting correct SQL connection details, visit the website ( http://127.0.0.1/ ), it will generate a mysql schema you should import to your Blue Server database. You can find it in [blue-util](https://github.com/bluebase-project/blue-util/) repository.

4: Follow the steps on the website and import the SQL schema for Znote AAC, and edit config.php with correct mysql details.

5: Enjoy Blue Website. You can look around [HERE](https://otland.net/forums/website-applications.118/) for plugins and resources to Blue Website, for instance various free templates to use.

6: Please note that you need PHP cURL enabled to make Paypal payments work.

7: You may need to change directory access rights of /engine/cache to allow writing.

### Bugs

Have found a bug? Please create an issue in our [bug tracker](https://github.com/bluebase-project/blue-website/issues)

### License

Blue Website is made available under the MIT License, thus this means that you are free
to do whatever you want, commercial, non-commercial, closed or open.
