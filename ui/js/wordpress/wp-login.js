/**
 * Custom Login Functionality
 * 
 * @title      Login
 * @desc       Login functionality
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
/* global stephino_rpg_data, top, self */
// Stephino RPG: Login
jQuery && jQuery(document).ready(function() { var $ = jQuery, bodyObject = $('body'); $('<div data-role="stephino-version"></div>').html(`<a href="${stephino_rpg_data.wp_url}" target="_blank">Stephino RPG v. <b>${stephino_rpg_data.game_ver}</b></a>`).appendTo('body'); jQuery('#rememberme').prop('checked', true); $('a').each(function() { if ($(this).attr('href').match(/wp\-login\.php/g)) { var url = new URL($(this).attr('href')); url.searchParams.set('redirect_to', stephino_rpg_data.ajax_url); url.searchParams.set('stephino-rpg-login', 1); $(this).attr('href', url.toString()); } }); $('form').each(function() { var url = new URL($(this).attr('action')); url.searchParams.set('redirect_to', stephino_rpg_data.ajax_url); url.searchParams.set('stephino-rpg-login', 1); $(this).attr('action', url.toString()); }); var messageHolder = $('<div data-role="stephino-message-holder"></div>').appendTo('body'); if ($('#login .message').length) { window.setTimeout(function() { messageHolder.find('.message').slideUp(); }, 5000); } $('#login #login_error').click(function(e) { $(this).slideUp(); }); $('#login .message a, #login #login_error a, .privacy-policy-link').attr('target', '_blank'); $('#login .message, #login #login_error').detach().appendTo(messageHolder); $('#backtoblog, #login h1, .clear, #reg_passmail').remove(); ['circle', 'char', 'title'].forEach(function(value) { var content = ''; if ('title' === value) { content = `${stephino_rpg_data.game_name} <span>${stephino_rpg_data.game_desc}</span>`; } bodyObject.append(`<div data-role="stephino-login-${value}">${content}</div>`); }); var blobPath = 'M39.1,-50.4C49.1,-38.2,54.6,-24.4,53.1,-12.4C51.6,-0.4,43.2,9.8,38.2,25.3C33.1,40.8,31.5,61.5,22.1,68.1C12.7,74.7,-4.6,67.3,-23.9,61.9C-43.3,56.5,-64.8,53.2,-75.4,40.7C-85.9,28.2,-85.5,6.6,-74.2,-5.3C-62.8,-17.2,-40.5,-19.4,-26.3,-31C-12.1,-42.5,-6.1,-63.4,4.2,-68.4C14.5,-73.5,29.1,-62.7,39.1,-50.4Z'; bodyObject.append(`<div data-role="stephino-login-blob-separator"><svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><path fill="#ff0066" d="${blobPath}" transform="translate(100 100)" /></svg></div>`); window.setTimeout(function() { bodyObject.addClass('ready'); }, 250); $('input[type="button"], input[type="submit"]').each(function() { var replacement = $('<button class="st-btn"><div><span></span><span></span><span class="st-btn-txt"></span></div></button>'); replacement.find('.st-btn-txt').html($(this).val()); $(this).replaceWith(replacement); }); try { if (parent.location !== self.location) { bodyObject.append( jQuery('<span></span>') .attr('data-role', 'stephino-login-fullscreen') .click(function() { try { if (screenfull && screenfull.isEnabled) { !screenfull.isFullscreen && screenfull.request(); } } catch (e) { try { top.location.href = self.location.href; } catch (e) {} } }) ); } } catch (ei) {} try { if (top.location.host === self.location.host) { $('div.nsl-container-block .nsl-container-buttons a').each(function() { var provider = $(this).attr('data-provider'); var providerCapitalized = provider.charAt(0).toUpperCase() + provider.slice(1); $(this).find('.nsl-button-label-container').html(providerCapitalized); }); $('#nsl-custom-login-form-main').prepend(`<div data-role="stephino-social-separator">${stephino_rpg_data.i18n.label_or}</div>`); } else { throw '[stephino-rpg-login] Not the same host'; } } catch (e) { var fallbackUrl = self.location.href; var fallbackTarget = '_blank'; try { fallbackUrl = parent.location.href; fallbackTarget = '_top'; } catch (ef) {} var loginButton = $('<a class="st-btn"><div><span></span><span></span><span class="st-btn-txt"></span></div></a>'); loginButton .attr('href', fallbackUrl) .attr('target', fallbackTarget) .find('.st-btn-txt').html(stephino_rpg_data.i18n.label_log_in); $('#login').html('').append(loginButton); }});

/*!
 * Screenfull v5.0.2
 * https://github.com/sindresorhus/screenfull.js/
 * 
 * Copyright (c) Sindre Sorhus
 * Licensed under MIT
 */
!function(){"use strict";var u="undefined"!=typeof window&&void 0!==window.document?window.document:{},e="undefined"!=typeof module&&module.exports,c=function(){for(var e,n=[["requestFullscreen","exitFullscreen","fullscreenElement","fullscreenEnabled","fullscreenchange","fullscreenerror"],["webkitRequestFullscreen","webkitExitFullscreen","webkitFullscreenElement","webkitFullscreenEnabled","webkitfullscreenchange","webkitfullscreenerror"],["webkitRequestFullScreen","webkitCancelFullScreen","webkitCurrentFullScreenElement","webkitCancelFullScreen","webkitfullscreenchange","webkitfullscreenerror"],["mozRequestFullScreen","mozCancelFullScreen","mozFullScreenElement","mozFullScreenEnabled","mozfullscreenchange","mozfullscreenerror"],["msRequestFullscreen","msExitFullscreen","msFullscreenElement","msFullscreenEnabled","MSFullscreenChange","MSFullscreenError"]],r=0,l=n.length,t={};r<l;r++)if((e=n[r])&&e[1]in u){for(r=0;r<e.length;r++)t[n[0][r]]=e[r];return t}return!1}(),l={change:c.fullscreenchange,error:c.fullscreenerror},n={request:function(t){return new Promise(function(e,n){var r=function(){this.off("change",r),e()}.bind(this);this.on("change",r);var l=(t=t||u.documentElement)[c.requestFullscreen]();l instanceof Promise&&l.then(r).catch(n)}.bind(this))},exit:function(){return new Promise(function(e,n){if(this.isFullscreen){var r=function(){this.off("change",r),e()}.bind(this);this.on("change",r);var l=u[c.exitFullscreen]();l instanceof Promise&&l.then(r).catch(n)}else e()}.bind(this))},toggle:function(e){return this.isFullscreen?this.exit():this.request(e)},onchange:function(e){this.on("change",e)},onerror:function(e){this.on("error",e)},on:function(e,n){var r=l[e];r&&u.addEventListener(r,n,!1)},off:function(e,n){var r=l[e];r&&u.removeEventListener(r,n,!1)},raw:c};c?(Object.defineProperties(n,{isFullscreen:{get:function(){return Boolean(u[c.fullscreenElement])}},element:{enumerable:!0,get:function(){return u[c.fullscreenElement]}},isEnabled:{enumerable:!0,get:function(){return Boolean(u[c.fullscreenEnabled])}}}),e?module.exports=n:window.screenfull=n):e?module.exports={isEnabled:!1}:window.screenfull={isEnabled:!1}}();