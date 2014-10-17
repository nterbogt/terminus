<?php
namespace Terminus;

use Guzzle\Http\Client as Browser;
use \Terminus\Fixtures;
/**
 * Handles requests made by terminus
 *
 * This is simply a class to manage the interactions between Terminus and Guzzle
 * ( the HTTP library Terminus uses ). This class should eventually evolve to
 * manage all requests to external resources such. Eventually we could even log
 * requests in debug mode.
 */

class Request {
  public $request; // Guzzle\Http\Message\Request object
  public $browser; // Guzzle\Http\Client object
  public $response; // most recent Guzzle\Http\Message\Response
  public $responses = array();

  public static function send( $url, $method, $data = array() ) {
    // create a new Guzzle\Http\Client
    $browser = new Browser;
    $options = array();
    $options['allow_redirects'] = @$data['allow_redirects'] ?: false;
    $options['json'] = @$data['json'] ?: false;
    if( @$data['body'] ) {
      $options['body'] = $data['body'];
    }
    $options['verify'] = false;

    $request = $browser->createRequest($method, $url, null, null, $options );

    if( !empty($data['postdata']) ) {
      foreach( $data['postdata'] as $k=>$v ) {
        $request->setPostField($k,$v);
      }
    }

    if( !empty($data['cookies']) ) {
      foreach( $data['cookies'] as $k => $v ) {
        $request->addCookie($k,$v);
      }
    }

    if( !empty($data['headers']) ) {
      foreach( $data['headers'] as $k => $v ) {
        $request->setHeader($k,$v);
      }
    }

    if ( getenv("debug") === 1 ) {
      print "####### DEBUG #######".PHP_EOL;
      print $request->getRawHeaders();
      print_r($data);
      print "####### END DEBUG #####".PHP_EOL.PHP_EOL;
    }

    if ( getenv("BUILD_FIXTURES") ) {
      Fixtures::put("request_headers", $request->getRawHeaders());
    }

    $response = $request->send();

    if ( getenv("BUILD_FIXTURES") ) {
      Fixtures::put("response", serialize( $response ) );
    }

    return $response;
  }

 }
