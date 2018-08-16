<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui container">
	<div class="ui header">Chronoforms 6 instructions set guide</div>
	<div class="ui relaxed divided list">
		
		<div class="item">
			<i class="large desktop middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{view:VIEW_NAME}</a>
				<div class="description">Display view, view must be created under the <div class="ui label blue">Sections</div> tab.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large code middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{function:FUNCTION_NAME} or {fn:FUNCTION_NAME}</a>
				<div class="description">Run a function, function must be created under the <div class="ui label blue">Events</div> tab.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large calculator middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{var:FUNCTION_NAME}</a>
				<div class="description">Get a function return result, function should have been called already using the {function:abc} call.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large lightning middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{event:EVENT_NAME}</a>
				<div class="description">Display an event contents, event contents must be set and may contain html code or other C6 instructions.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large keyboard middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{data:PARAMETER_NAME}</a>
				<div class="description">Get a url parameter or a form input value.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large calendar middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{date:}</a>
				<div class="description">Get the current datetime in mySQL format Y-m-d H:i:s.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large user middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{user:USER_PARAM}</a>
				<div class="description">Get the current logged in user param value, e.g: {user:id} or {user:username}.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large hashtag middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{value:STRING}</a>
				<div class="description">return the passed string as a PHP value.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large suitcase middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{session:PARAMETER_NAME}</a>
				<div class="description">Get a session paramater's value, session data can be set using the "Save to session" function.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large external middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{redirect:EVENT_NAME} or {redirect:URL}</a>
				<div class="description">Redirect to an event or to another URL.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large link middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{url:} or {url:EVENT_NAME}</a>
				<div class="description">Get the connection's url or the url of one of the events.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large info green middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{error:TEXT} or {success:TEXT} or {info:TEXT} or {warning:TEXT}</a>
				<div class="description">Display the provided text in a message of the type error/success/info/warning.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large plug red middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{stop:} or {end:}</a>
				<div class="description">Stop any processing at this point.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large translate middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{lang:TEXT} or {l:TEXT}</a>
				<div class="description">Localize the provided text using the active site language, the translations must be provided under the <div class="ui label blue">Locales</div> tab.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large map middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{debug:}</a>
				<div class="description">Display the current event debug data.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large privacy middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{uuid:}</a>
				<div class="description">Return a unique v4 uuid string.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large magic middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{rand:}</a>
				<div class="description">Return a random number.</div>
			</div>
		</div>
		
		<div class="item">
			<i class="large globe middle aligned icon"></i>
			<div class="content">
				<a class="ui header small">{ip:}</a>
				<div class="description">Get the client's ip address.</div>
			</div>
		</div>
	</div>
</div>