<?php

require_once __DIR__ . '/vendor/autoload.php';
use deemru\WavesKit;

if( file_exists( __DIR__ . '/config.php' ) )
    require_once __DIR__ . '/config.php';
else
    require_once __DIR__ . '/config.sample.php';

$wk = new WavesKit();
$wkScript = new WavesKit( $wk->getChainId(), false );
$wk->setPublicKey( 'CanMZkMmTYKHVLqKXrVMXTHuyUtag853a8ti9m1LHYFa' );
$wkScript->setPublicKey( $wk->getPublicKey() );

$wk->log( 's', 'Huger started' );
$wk->log( 'i', "miner = $miner" );

function zerobits( $id )
{
    if( $id[0] !== chr( 0 ) || $id[1] !== chr( 0 ) )
        return 0;

    $zeros = 16;
    for( $i = 2; $i < 8; $i++ )
    {
        if( $id[$i] === chr( 0 ) )
            $zeros += 8;
        else
        {
            $c = ord( $id[$i] );
            if( $c & 0x80 )
                $zeros += 0;
            elseif( $c & 0x40 )
                $zeros += 1;
            elseif( $c & 0x20 )
                $zeros += 2;
            elseif( $c & 0x10 )
                $zeros += 3;
            elseif( $c & 0x08 )
                $zeros += 4;
            elseif( $c & 0x04 )
                $zeros += 5;
            elseif( $c & 0x02 )
                $zeros += 6;
            else
                $zeros += 7;
            break;
        }
    }

    return $zeros;
}

function incrementer( &$val )
{
    for( $i = 0;; $i++ )
    {
        $c = ord( $val[$i] );

        if( $c < 255 )
        {
            $val[$i] = chr( $c + 1 );
            return;
        }

        $val[$i] = chr( 0 );
    }
}

$difficulty_last = 0;

for( ;; )
{
    // script set difficulty
    {
        $data = [];
        for( $i = 20; $i <= 48; $i++ )
        {
            $data['difficulty'] = $i;
            $data['lastUpdateHeight'] = $wk->height();
            $data['lastUpdateBalance'] = $wk->balance()['BXSAEa9jm9Qrkmn2XPqqKBkukZoBkJ8YpQ9EZywjdnnx']['balance'];

            $datatx = $wkScript->txData( $data, [ 'fee' => 500000 ] );
            if( false !== ( $stx = $wkScript->txBroadcast( $datatx ) ) )
                $wk->log( 's', "set difficulty = $i" );
        }
    }

    // mining
    for( ;; )
    {
        $tx = $wk->txTransfer( $miner, 1, 'BXSAEa9jm9Qrkmn2XPqqKBkukZoBkJ8YpQ9EZywjdnnx', [ 'fee' => 500000 ] );
        $val = random_bytes( 7 ) . chr( mt_rand( 0, 32 ) );
        $tx['attachment'] = $wk->base58Encode( $val );
        $tx['timestamp'] = $wk->timestamp( true );
        $body = $wk->txBody( $tx );
        $body_start = substr( $body, 0, -8 );

        $difficulty = $wk->getData( 'difficulty' );
        if( $difficulty_last !== $difficulty )
            $wk->log( 'i', "difficulty = $difficulty" );
        $difficulty_last = $difficulty;
        $stx = true;

        for( $i = 0; $i < 13333337; $i++ )
        {
            $id = $wk->blake2b256( $body_start . $val );
            if( zerobits( $id ) >= $difficulty )
            {
                $tx['attachment'] = $wk->base58Encode( $val );
                $stx = $wk->txBroadcast( $tx );
                if( $stx === false )
                    break;
                $wk->log( 's', "$miner ($difficulty)" );
                break;
            }
            incrementer( $val );
        }

        if( $stx === true )
            $wk->log( 'i', "calculating... ($difficulty)" );

        if( $stx === false )
            break;
    }
}
