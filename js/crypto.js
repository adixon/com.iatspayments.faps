/* 
 * custom js so we can use the FAPS cryptojs script
 *
 */

/*jslint indent: 2 */
/*global CRM, ts */

cj(function ($) {
  'use strict';
  // convert placeholder card field container to required id
  $('.credit_card_info-section').first().attr('id','checkout-embed');
});
