/**
 * Huiji SteamAchievement getter.
 */

mw.__proto__.steamAchievement = function (option) {
	if (typeof option != "object" || option == null){
		option = {};	
	}
	var config = {
		page : option.page || '',
		key : option.key || '',
		language :option.language || 'schinese'
	}
	if (config.page == '' || ( mw.config.get('wgPageName') === config.page && mw.config.get('wgAction') === 'View' ) ) {
		var data = {
			achievement: []
		}
		var myTemplate = mw.template.get('ext.socialprofile.steamachievement', 'SteamAchievement.mustache');
		var API = 'http://steam.cdn.huijiwiki.com/ISteamUserStats/GetSchemaForGame/v2/?key=395E0A115E7986C8783646355E878444&appid=' + config.key + '&l=' + config.language;
		var xmlHttp = new XMLHttpRequest();
		xmlHttp.open("get", API, false);
		xmlHttp.onreadystatechange = function() {
			if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
				var data1 = JSON.parse(xmlHttp.responseText);
				var theData = data1.game.availableGameStats.achievements;
				for (var i = 0; i < theData.length; i++) {
					data.achievement[i] = {
						'image': theData[i].icon,
						'imageAlt': theData[i].name,
						'heading': theData[i].displayName,
						'content': theData[i].description
					}
				}
			}
		}
		xmlHttp.send(null);
		$html = myTemplate.render(data);
		return $html;
	}
}
