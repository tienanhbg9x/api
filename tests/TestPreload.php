<?php
/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 9/21/2018
 * Time: 10:47 AM
 */
function send_http2_link_header( $items ) {
	$links = array();
	foreach( $items as $as => $urls ) {
		foreach ( $urls as $url ) {
			$links[] = sprintf(
				'<%s>; rel=preload; as=%s',
				$url,
				$as
			);
		}
	}
	if ( $links ) {
        echo "Link: " . implode( ", ", array_unique( $links ) );
		//header( "Link: " . implode( ", ", array_unique( $links ) ), false );
	}
}

$items = [
    "script" => [
        "https://muazi.vn/_nuxt/app.9c9c11e10ecf8253ce88.js"
    ],
    "style" => [
        "https://muazi.vn/assets/bootstrap/css/bootstrap.min.css"
    ],
    "font" => [
        "https://muazi.vn/assets/bootstrap/css/bootstrap.min.css"
    ]
];
send_http2_link_header($items);