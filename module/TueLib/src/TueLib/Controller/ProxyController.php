<?php
/**
 * Proxy Controller Module
 *
 * PHP version 5
 *
 * Copyright (C) Universitätsbiblothek Tübingen 2015.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category TueLib
 * @author   Johannes Ruscheinski <johannes.ruscheinski@uni-tuebingen.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 */
namespace TueLib\Controller;

use VuFind\Exception\Forbidden as ForbiddenException;

/**
 * This controller handles global web proxy functionality.
 *
 * @package  Controller
 * @author   Johannes Ruscheinski <johannes.ruscheinski@uni-tuebingen.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 */
class ProxyController extends \VuFind\Controller\AbstractBase
{
    const DNB_REGEX = 'http://services.dnb.de/fize-service/gvr/.*';
    const WHITE_LIST_REGEX = ProxyController::DNB_REGEX;

    public function loadAction()
    {
        $requestUri = $this->getRequest()->getUri()->getQuery();
        $url = urldecode(strstr($requestUri, 'http'));
        if (ereg(ProxyController::WHITE_LIST_REGEX, $url) !== FALSE) {
            $client = $this->getServiceLocator()->get('VuFind\Http')->createClient();
            return $client->setUri($url)->send();
        } else {
            throw new ForbiddenException('Access denied.');
        }
    }
}
