<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @copyright 2015, Chris Zuber
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace shgysk8zer0\Core;
/**
 * A custom exception where the message is considered non-sensitive information
 * that can be shown to users and the code is an HTTP status code
 */
final class HTTPException extends \RuntimeException
{
	// Continue is not valid
	const CONT                             = 100;
	const SWITCHING_PROTOCOLS              = 101;
	const PROCESSING                       = 102;

	const OK                               = 200;
	const CREATED                          = 201;
	const ACCEPTED                         = 202;
	const NON_AUTHORITATIVE                = 203;
	const NO_CONTENT                       = 204;
	const RESET_CONTENT                    = 205;
	const PARTIAL_CONTENT                  = 206;
	const MULTI_STATUS                     = 207;
	const ALREADY_REPORTED                 = 208;
	const IM_USED                          = 226;

	const MULTIPLE_CHOICES                 = 300;
	const MOVED_PERMANENTLY                = 301;
	const FOUND                            = 302;
	const SEE_OTHER                        = 303;
	const NOT_MODIFIED                     = 304;
	const USE_PROXY                        = 305;
	const SWITCH_PROXY                     = 306;
	const TEMPORARY_REDIRECT               = 307;
	const PERMANENT_REDIRECT               = 308;

	const BAD_REQUEST                      = 400;
	const UNAUTHORIZED                     = 401;
	const PAYMENT_REQUIRED                 = 402;
	const FORBIDDEN                        = 403;
	const NOT_FOUND                        = 404;
	const METHOD_NOT_ALLOWED               = 405;
	const NOT_ACCEPTABLE                   = 406;
	const PROXY_AUTHENTICATION_REQUIRED    = 407;
	const REQUEST_TIMEOUT                  = 408;
	const CONFLICT                         = 409;
	const GONE                             = 410;
	const LENGTH_REQUIRED                  = 411;
	const PRECONDITION_FAILED              = 412;
	const PAYLOAD_TOO_LARGE                = 413;
	const URI_TOO_LONG                     = 414;
	const UNSUPPORTED_MEDIA_TYPE           = 415;
	const RANGE_NOT_SATISFIABLE            = 416;
	const EXPECTATION_FAILED               = 417;
	const IM_A_TEAPOT                      = 418;
	const AUTHENTICATION_TIMEOUT           = 419;
	const MISDIRECTED_REQUEST              = 421;
	const UNPROCESSABLE_ENTITY             = 422;
	const LOCKED                           = 423;
	const FAILED_DEPENDENCY                = 424;
	const UPGRADE_REQUIRED                 = 426;
	const PRECONDITION_REQUIRED            = 428;
	const TOO_MANY_REQUESTS                = 429;
	const REQUEST_HEADER_FILEDS_TOO_LARGE  = 431;
	const UNAVAILABLE_FOR_LEGAL_REASONS    = 451;

	const INTERNAL_SERVER_ERROR            = 500;
	const NOT_IMPLEMENTED                  = 501;
	const BAD_GATEWAY                      = 502;
	const SERVICE_UNAVAILABLE              = 503;
	const GATEWAY_TIMEOUT                  = 504;
	const HTTP_VERSION_NOT_SUPPORTED       = 505;
	const VARIANT_ALSO_NEGOTIATES          = 506;
	const INSUFFICIENT_STORAGE             = 507;
	const LOOP_DETECTED                    = 508;
	const NOT_EXTENDED                     = 510;
	const NETWORK_AUTHENTICATION_REQUIERED = 511;

	public function __construct(
		String     $message,
		Int        $code     = self::OK,
		\Throwable $previous = null
	)
	{
		if ($code < 100 or $code > 511){
			throw new \InvalidArgumentException("{$code} is not a valid HTTP status code");
		}
		parent::__construct($message, $code, $previous);
	}
}
