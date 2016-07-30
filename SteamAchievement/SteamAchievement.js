/**
 * Huiji SteamAchievement getter.
 * @example: 
 */
// $(document).ready(function() {
// 	mw.steamAchievement(['智慧祝福', '350640']);
// });

mw.__proto__.steamAchievement = function (member) {
	if (mw.config.get('wgPageName') == member[0]) {
		var data = {
			achievement: []
		}
		var myTemplate = mw.template.get('ext.socialprofile.steamachievement', 'SteamAchievement.mustache');
		var API = 'http://steam.cdn.huijiwiki.com/ISteamUserStats/GetSchemaForGame/v2/?key=395E0A115E7986C8783646355E878444&appid=' + member[1] + '&l=schinese';
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