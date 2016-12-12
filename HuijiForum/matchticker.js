
mw.matchticker = function ( option, callback ){
	var game = option.game || 'dota2';
	var ul = option.upcoming || '100';
	var fl = option.finished || '100';
	var getCustom = option.custom || function(){return ''};
 	var myTemplate = mw.template.get('ext.socialprofile.matchticker', 'matchticker.mustache');
 	var grabMatchTitle = function(url){
 		var res = [];
 		var match = url.match(/\/tournaments\/((?:\d+-[^\/]*?\/)+)matches\//);
 		console.log(match);
 		var reg = /(?:\d+-([^\/]*?)\/)+?/g;
 		var match2 = reg.exec(match[1]);
 		while(match2 != null){
 			console.log(match2);
 			res.push({matchtitle : match2[1]});
 			match2 = reg.exec(match[1]);
 		}
 		return res;
 	}
	var countryToFlag = function(en){
		var table = {
			"China":"中国",
			"Canada":"加拿大",
			"Taiwan":"中华台北",
			"Hong Kong":"中国香港",
			"Netherlands":"荷兰",
			"Sweden":"瑞典",
			"Korea, Republic of":"韩国",
			"Chile":"智利",
			"Brazil":"巴西",
			"Denmark":"丹麦",
			"United Kingdom":"英国",
			"Russian Federation":"俄罗斯",
			"Malaysia":"马来西亚",
			"United States":"美国",
			"France":"法国",
			"Ukraine":"乌克兰",
			"Thailand":"泰国",
			"Philippines":"菲律宾",
			"Peru":"秘鲁",
			"Poland":"波兰",
			"Georgia":"格鲁吉亚",
			"Czech Republic":"捷克",
			"Australia":"澳大利亚",
			"Austria":"奥地利",
			"Singapore":"新加坡",
			"Germany":"德国",
			"Argentina":"阿根廷",
			"Bulgaria":"保加利亚",
			"Romania":"罗马尼亚",
			"Mongolia":"蒙古",
			"Serbia":"塞尔维亚",
			"United Arab Emirates":"阿联酋",
			"Jordan":"约旦",
			"India":"印度",
			"Turkey":"土耳其",
			"Vietnam":"越南",
			"Indonesia":"印度尼西亚",
			"Finland":"芬兰",
			"Italy":"意大利",
			"Belarus":"白俄罗斯",
			"New Zealand":"新西兰",
			"Macau":"澳门",
			"Japan":"日本",
			"Croatia":"克罗地亚",
			"Latvia":"拉脱维亚",
			"Pakistan":"巴基斯坦",
			"Israel":"以色列",
			"Lebanon":"黎巴嫩",
			"Kazakhstan":"哈萨克斯坦",
			"Venezuela":"委内瑞拉",
			"Mexico":"墨西哥",
			"Greece":"希腊",
			"Bosnia":"波黑",
			"Iran":"伊朗",
			"Estonia":"爱沙尼亚",
			"Guatemala":"危地马拉",
			"Ireland":"爱尔兰",
			"Tunysia":"突尼斯",
			"El Salvador":"萨尔瓦多",
			"Belgium":"比利时",
			"Armenia":"亚美尼亚",
			"Azerbaijan":"阿塞拜疆",
			"Bahrain":"巴林",
			"Bolivia":"玻利维亚",
			"Macedonia":"马其顿",
			"Slovak Republic":"斯洛伐克",
			"Scotland":"苏格兰",
			"Portugal":"葡萄牙",
			"Switzerland":"瑞士",
			"Spain":"西班牙",
			"Puerto Rico":"波多黎各",
			"Saudi Arabia":"沙特阿拉伯",
			"Other":"其他国家",
			"Europe":"欧洲"
		}
		return (table[en] || en);
	}
	$.ajax({
		url: 'http://wc.huiji.wiki:5985'+"/"+game,
		type: 'GET',
		success: function (res){
			var ongoing = [], upcoming = [], finished = [];
			var data = JSON.parse(res);
			for (var i in data){
				if (data[i].status == "live"){
					ongoing.push({
						Player1: data[i].home.name,
						player1country: countryToFlag(data[i].home.country),
						player1rank: data[i].home.rank?data[i].home.rank+"位":"暂无",
						player1score: data[i].home.score || 0,
						Player2: data[i].away.name,
						player2country: countryToFlag(data[i].away.country),
						player2rank: data[i].away.rank?data[i].away.rank+"位":"暂无",
						player2score: data[i].away.score || 0,
						time: new Date(data[i].datetime*1000).toLocaleString(),
						type: data[i].rounds || "Best of 1",
						matches: grabMatchTitle(data[i].url),
						custom: getCustom(data[i])
					});
				}
				if (data[i].status=="Upcoming"){
					if (upcoming.length >= ul){
						continue;
					}
					upcoming.push({
						Player1: data[i].home.name,
						player1country: countryToFlag(data[i].home.country),
						player1rank: data[i].home.rank?data[i].home.rank+"位":"暂无",
						player1score: data[i].home.score || 0,
						Player2: data[i].away.name,
						player2country: countryToFlag(data[i].away.country),
						player2rank: data[i].away.rank?data[i].away.rank+"位":"暂无",
						player2score: data[i].away.score || 0,
						time: new Date(data[i].datetime*1000).toLocaleString(),
						type: data[i].rounds || "Best of 1",
						matches: grabMatchTitle(data[i].url),
						first: i===0,
						custom: getCustom(data[i])
					});

				}
				if (data[i].status=="Complete"){
					if (finished.length >= fl){
						continue;
					}
					finished.push({
						Player1: data[i].home.name,
						player1country: countryToFlag(data[i].home.country),
						player1rank: data[i].home.rank?data[i].home.rank+"位":"暂无",
						player1score: data[i].home.score || 0,
						Player2: data[i].away.name,
						player2country: countryToFlag(data[i].away.country),
						player2rank: data[i].away.rank?data[i].away.rank+"位":"暂无",
						player2score: data[i].away.score || 0,
						time: new Date(data[i].datetime*1000).toLocaleString(),
						type: data[i].rounds || "Best of 1",
						matches: grabMatchTitle(data[i].url),
						custom: getCustom(data[i])
					});
				}

			}
			$html = myTemplate.render({ongoing: ongoing, upcoming:upcoming, finished:finished});
			callback($html);	
		}
	})

}