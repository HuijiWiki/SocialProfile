function insertRecordIntoDB(url,navigatorInfo,fromSource,userId,userName,wikiSite,siteName,titleName,articleId) {
    jQuery.post(
        url,
        {
            navigatorInfo:navigatorInfo,
            fromSource:clearSourceUrl(fromSource),
            userId:userId,
            userName:userName,
            articleId:articleId,
            titleName:titleName,
            siteName:siteName,
            wikiSite:wikiSite,
        }
    )
}

function clearSourceUrl(sourceUrl){
    var e = new RegExp('^(?:(?:https?|ftp):)/*(?:[^@]+@)?([^:/#]+)'),
        matches = e.exec(sourceUrl);
    return matches ? matches[1]:sourceUrl;
}


function getViewRecordsFromUserIdGroupByWikiSite(userId,fromTime,toTime,callback){
    var url = 'http://test.huiji.wiki:50007/getViewRecordsFromUserIdGroupByWikiSite/';
    jQuery.post(
        url,
        {
            userId:userId,
            fromTime:fromTime,
            toTime:toTime,
        },
        function(data){
            //	console.log(data);
            if(callback != null) {
                callback(data);
            }else{
                return data;
            }
        }
    ).error(function(){
            //console.log("error");
            var errInfo = {'status':'fail'};
            if(callback != null){
                callback(errInfo);
            }else{
                return errInfo;
            }
        });
}

function getEditRecordsFromUserIdGroupByWikiSite(userId,fromTime,toTime,callback){
    var url = 'http://test.huiji.wiki:50007/getEditRecordsFromUserIdGroupByWikiSite/';
    jQuery.post(
        url,
        {
            userId:userId,
            fromTime:fromTime,
            toTime:toTime,
        },
        function(data){
            //	console.log(data);
            if(callback != null){
                callback(data);
            }else{
                return data;
            }
        }
    ).error(function(){
            //console.log("error");
            var errInfo = {'status':'fail'};
            if(callback != null) {
                callback(errInfo);
            }else{
                return errInfo;
            }
        });
}

function getEditorCountGroupByWikiSite(fromTime,toTime,callback){
    var url = 'http://test.huiji.wiki:50007/getEditorCountGroupByWikiSite/';
    jQuery.post(
        url,
        {
            fromTime:fromTime,
            toTime:toTime,
        },
        function(data){
            //	console.log(data);
            if(callback != null) {
                callback(data);
            }else{
                return data;
            }
        }
    ).error(function(){
            //console.log("error");
            var result = {'status':'fail'};
            if(callback != null) {
                callback(result);
            }else{
                return result;
            }
        });
}

function getEditRecordsOnWikiSiteFromUserIdGroupByDay(userId,wikiSite,fromTime,toTime,callback)
{
    var url = 'http://test.huiji.wiki:50007/getEditRecordsOnWikiSiteFromUserIdGroupByDay/';
    jQuery.post(
        url,
        {
            userId:userId,
            wikiSite:wikiSite,
            fromTime:fromTime,
            toTime:toTime,
        },
        function(data){
            //	console.log(data);
            if(callback != null) {
                callback(data);
            }else{
                return data;
            }
        }
    ).error(function(){
            //console.log("error");
            var result = {'status':'fail'};
            if(callback != null){
                callback();
            }else{
                return result;
            }
        });

}



function getViewRecordsOnWikiSiteFromUserIdGroupByDay(userId,wikiSite,fromTime,toTime,callback)
{
    var url = 'http://test.huiji.wiki:50007/getViewRecordsOnWikiSiteFromUserIdGroupByDay/';
    jQuery.post(
        url,
        {
            userId:userId,
            wikiSite:wikiSite,
            fromTime:fromTime,
            toTime:toTime
        },
        function(data){
//			console.log(data);
            if(callback != null) {
                callback(data);
            }else{
                data
                return data;
            }
        }
    ).error(function(){
            //console.log("error");
            var result = {'status':'fail'};
            if(callback != null){
                callback();
            }else{
                return result;
            }
        });

}
jQuery( document ).ready( function() {

    // Morris.Area({
    //     element: 'morris-area-chart',
    //     data: [{
    //         period: '2010 Q1',
    //         iphone: 2666,
    //         ipad: null,
    //         itouch: 2647
    //     }, {
    //         period: '2010 Q2',
    //         iphone: 2778,
    //         ipad: 2294,
    //         itouch: 2441
    //     }, {
    //         period: '2010 Q3',
    //         iphone: 4912,
    //         ipad: 1969,
    //         itouch: 2501
    //     }, {
    //         period: '2010 Q4',
    //         iphone: 3767,
    //         ipad: 3597,
    //         itouch: 5689
    //     }, {
    //         period: '2011 Q1',
    //         iphone: 6810,
    //         ipad: 1914,
    //         itouch: 2293
    //     }, {
    //         period: '2011 Q2',
    //         iphone: 5670,
    //         ipad: 4293,
    //         itouch: 1881
    //     }, {
    //         period: '2011 Q3',
    //         iphone: 4820,
    //         ipad: 3795,
    //         itouch: 1588
    //     }, {
    //         period: '2011 Q4',
    //         iphone: 15073,
    //         ipad: 5967,
    //         itouch: 5175
    //     }, {
    //         period: '2012 Q1',
    //         iphone: 10687,
    //         ipad: 4460,
    //         itouch: 2028
    //     }, {
    //         period: '2012 Q2',
    //         iphone: 8432,
    //         ipad: 5713,
    //         itouch: 1791
    //     }],
    //     xkey: 'period',
    //     ykeys: ['iphone', 'ipad', 'itouch'],
    //     labels: ['iPhone', 'iPad', 'iPod Touch'],
    //     pointSize: 2,
    //     hideHover: 'auto',
    //     resize: true
    // });

    // Morris.Donut({
    //     element: 'morris-donut-chart',
    //     data: [{
    //         label: "Download Sales",
    //         value: 12
    //     }, {
    //         label: "In-Store Sales",
    //         value: 30
    //     }, {
    //         label: "Mail-Order Sales",
    //         value: 20
    //     }],
    //     resize: true
    // });

    // Morris.Bar({
    //     element: 'morris-bar-chart',
    //     data: [{
    //         y: '2006',
    //         a: 100,
    //         b: 90
    //     }, {
    //         y: '2007',
    //         a: 75,
    //         b: 65
    //     }, {
    //         y: '2008',
    //         a: 50,
    //         b: 40
    //     }, {
    //         y: '2009',
    //         a: 75,
    //         b: 65
    //     }, {
    //         y: '2010',
    //         a: 50,
    //         b: 40
    //     }, {
    //         y: '2011',
    //         a: 75,
    //         b: 65
    //     }, {
    //         y: '2012',
    //         a: 100,
    //         b: 90
    //     }],
    //     xkey: 'y',
    //     ykeys: ['a', 'b'],
    //     labels: ['Series A', 'Series B'],
    //     hideHover: 'auto',
    //     resize: true
    // });
 // 路径配置
        require.config({
            paths: {
                echarts: 'http://echarts.baidu.com/build/dist'
            }
        });
        
        // use
        require(
            [
                'echarts',
                'echarts/chart/line', // 使用柱状图就加载bar模块，按需加载
                'echarts/chart/bar' // 使用柱状图就加载bar模块，按需加载
            ],
            function (ec) {
                // 基于准备好的dom，初始化echarts图表
                var  myChart = ec.init(document.getElementById('morris-area-echart'));
                var  option = {
                        tooltip : {
                            trigger: 'axis'
                        },
                        legend: {
                            data:['网站排名','关注人数','浏览次数','编辑次数']
                        },
                        toolbox: {
                            show : true,
                            feature : {
                                mark : {show: false},
                                dataView : {show: true, readOnly: true},
                                magicType : {show: true, type: ['line', 'bar', 'tiled']},
                                // magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                                restore : {show: true},
                                saveAsImage : {show: true}
                            }
                        },
                        calculable : true,
                        xAxis : [
                            {
                                type : 'category',
                                boundaryGap : false,
                                data : []
                            }
                        ],
                        yAxis : [
                            {
                                type : 'value'
                                // min: 0,
                                // max: 1000,
                                // splitNumber: 500
                                // scale : true,
                                // splitNumber : 0,100
                                // show : false
                            }
                        ],
                        series : [
                            {
                                name:'网站排名',
                                type:'line',
                                // stack: '总量',
                                // color:'red',
                                itemStyle:{
                                    normal:{
                                        lineStyle:{
                                            color:'black',
                                            width:3
                                        }
                                    }
                                },
                                data:[],

                                // 系列中的数据标注内容 series.markPoint  
                                markPoint:{  
                                    data:[  
                                        {type:'max',name:'最大值'},  
                                        {type:'min',name:'最小值'}  
                                    ]  
                                },  
                                //系列中的数据标线内容 series.markLine  
                                markLine:{  
                                    data:[  
                                        {type:'average',name:'平均值'}  
                                    ]  
                                }  
                            },
                            {
                                name:'关注人数',
                                type:'line',
                                // stack: '总量',
                                data:[],
                                // 系列中的数据标注内容 series.markPoint
                                markPoint:{
                                    data:[
                                        {type:'max',name:'最大值'},
                                        {type:'min',name:'最小值'}
                                    ]
                                },
                                //系列中的数据标线内容 series.markLine
                                markLine:{
                                    data:[
                                        {type:'average',name:'平均值'}
                                    ]
                                }
                            },
                            {
                                name:'浏览次数',
                                type:'line',
                                // stack: '总量',
                                data:[],
                                //系列中的数据标注内容 series.markPoint
                                markPoint:{
                                    data:[
                                        {type:'max',name:'最大值'},
                                        {type:'min',name:'最小值'}
                                    ]
                                },
                                //系列中的数据标线内容 series.markLine
                                markLine:{
                                    data:[
                                        {type:'average',name:'平均值'}
                                    ]
                                }
                            },
                            {
                                name:'编辑次数',
                                type:'line',
                                // stack: '总量',
                                data:[],
                                //系列中的数据标注内容 series.markPoint
                                markPoint:{
                                    data:[
                                        {type:'max',name:'最大值'},
                                        {type:'min',name:'最小值'}
                                    ]
                                },
                                //系列中的数据标线内容 series.markLine
                                markLine:{
                                    data:[
                                        {type:'average',name:'平均值'}
                                    ]
                                }
                            }
                        ]
                    };
                // };
                // 为echarts对象加载数据

                getViewRecordsOnWikiSiteFromUserIdGroupByDay('-1','','','',updateData);
                function updateData(data){
                    if (data.status == 'success'){
                        console.log(data.result);
                        var res = data.result;

                        for(var i=0;i<=3;i++){
                            option.xAxis[0].data[i]=res[i]._id;
                            option.series[0].data[i]=res[i].value;
                        }
                        myChart.setOption(option,false);
                        console.log(option.xAxis[0].data);
                        console.log(option.series[0].data);
                    }
                }
            }
        );
});
