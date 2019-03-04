# Huger

[![php-v](https://img.shields.io/packagist/php-v/deemru/waveskit.svg)](https://packagist.org/packages/deemru/waveskit) [![license](https://img.shields.io/github/license/deemru/Huger.svg)](https://packagist.org/packages/deemru/Huger)

[Huger](https://github.com/deemru/Huger) is a miner for Hugo: [Proof-of-Work on Waves](https://forum.wavesplatform.com/t/proof-of-work-on-waves/11465)

- Fully automated miner
- Just set your address or alias
- Powered by [deemru/WavesKit](https://github.com/deemru/WavesKit) framework

## Usage

- `composer install`
- Edit `config.sample.php`
- Run `php Huger.php`

## Notice

- Single-threaded
- Run more instances if you have more CPUs
- Results on PHP < 7.2 will be negligible ([Sodium](http://php.net/manual/en/book.sodium.php) available on PHP >= 7.2)
