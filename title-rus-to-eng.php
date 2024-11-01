<?php
/*
Plugin Name: Title Rus to Eng
Plugin URI: http://zetrider.ru/title_rus_to_eng/
Description: Автоматический перевод заголовка постов и страниц с Русского на Английский
Version: 1.0
Author: ZetRider
Author URI: http://zetrider.ru
Author Email: ZetRider@bk.ru
*/
/*  Copyright 2014  zetrider  (email: zetrider@bk.ru)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function title_rus_to_eng_page() {
	add_options_page( 'Перевод заголовков', 'Перевод заголовков', 'manage_options', 'title_rus_to_eng', 'title_rus_to_eng_setting');
} add_action('admin_menu', 'title_rus_to_eng_page');

function title_rus_to_eng_setting() {
?>
<div class="wrap">
	<h2>Перевод заголовков</h2>
	
	<div class="updated">
		<p>Бесплатный API ключ здесь: <a href="http://api.yandex.ru/key/form.xml?service=trnsl" target="blank">http://api.yandex.ru/key/form.xml?service=trnsl</a></p>
	</div>
	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">API-ключ Яндекс</th>
					<td><input type="text" name="title_rus_to_eng_api" value="<?php form_option('title_rus_to_eng_api'); ?>"></td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="title_rus_to_eng_api" />
		<input type="submit" name="update" class="button-primary" value="Сохранить API Key">
	</form>
	
	<hr>
	<script type="text/javascript">
	jQuery(document).ready( function($) {
		YaCode = [];
		YaCode[200] = 'Операция выполнена успешно';
		YaCode[401] = 'Неправильный ключ API';
		YaCode[402] = 'Ключ API заблокирован';
		YaCode[403] = 'Превышено суточное ограничение на количество запросов';
		YaCode[404] = 'Превышено суточное ограничение на объем переведенного текста';
		YaCode[413] = 'Превышен максимально допустимый размер текста';
		YaCode[422] = 'Текст не может быть переведен';
		YaCode[501] = 'Заданное направление перевода не поддерживается';
		
		$('.form-yandex-api').submit( function() {
			$.get(
				'https://translate.yandex.net/api/v1.5/tr.json/translate',
				$(this).serialize()
			).
			success(
				function(jdata){
					console.log(jdata);
					if(jdata.code == 200)
					{
						alert(jdata.text);
					}
				}
			).
			error(
				function(data, status, msg){
					alert(YaCode[data.responseJSON.code]);
				}
			);
			return false;
		});
	});
	</script>
	<form class="form-yandex-api">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">Проверить работу</th>
					<td>
					<input type="hidden" name="key" value="<?php form_option('title_rus_to_eng_api'); ?>">
					<input type="hidden" name="lang" value="ru-en">
					<input type="hidden" name="format" value="plain">
					<input type="text" name="text" value="Привет"> <input type="submit" class="button-primary" value="Отправить запрос">
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	
</div>
<?
}

function title_rus_to_eng( $title, $raw_title, $context ) {
	$ya_api = get_option('title_rus_to_eng_api');
	
	if($ya_api == '' OR $title == '' OR $context != 'save')
		return $title;
	
	$curl_data 				= array();
	$curl_data['key']		= $ya_api;		
	$curl_data['text']		= $title;
	$curl_data['lang']		= 'ru-en';
	$curl_data['format']	= 'plain';
	
	$curl_params 			= urldecode(http_build_query($curl_data));
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, 'https://translate.yandex.net/api/v1.5/tr.json/translate');
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_params);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	$return = curl_exec($curl);
	curl_close($curl);
	
	$json 	= json_decode($return, true);
	$code 	= $json['code'];

	if ( $code == 200 ) {
		return implode(' ', $json['text']);
	}
	else
	{
		return $title;
	}
}
add_filter( 'sanitize_title', 'title_rus_to_eng', 10, 3 );