// function insertRecordIntoDB(url,navigatorInfo,fromSource,userId,userName,wikiSite,siteName,titleName,articleId) {
//     jQuery.post(
//         url,
//         {
//             navigatorInfo:navigatorInfo,
//             fromSource:clearSourceUrl(fromSource),
//             userId:userId,
//             userName:userName,
//             articleId:articleId,
//             titleName:titleName,
//             siteName:siteName,
//             wikiSite:wikiSite,
//         }
//     )
// }

// function clearSourceUrl(sourceUrl){
//     var e = new RegExp('^(?:(?:https?|ftp):)/*(?:[^@]+@)?([^:/#]+)'),
//         matches = e.exec(sourceUrl);
//     return matches ? matches[1]:sourceUrl;
// }


// function getViewRecordsFromUserIdGroupByWikiSite(userId,fromTime,toTime,callback){
//     var url = 'http://test.huiji.wiki:50007/getViewRecordsFromUserIdGroupByWikiSite/';
//     jQuery.post(
//         url,
//         {
//             userId:userId,
//             fromTime:fromTime,
//             toTime:toTime,
//         },
//         function(data){
//             //	console.log(data);
//             if(callback != null) {
//                 callback(data);
//             }else{
//                 return data;
//             }
//         }
//     ).error(function(){
//             //console.log("error");
//             var errInfo = {'status':'fail'};
//             if(callback != null){
//                 callback(errInfo);
//             }else{
//                 return errInfo;
//             }
//         });
// }

// function getEditRecordsFromUserIdGroupByWikiSite(userId,fromTime,toTime,callback){
//     var url = 'http://test.huiji.wiki:50007/getEditRecordsFromUserIdGroupByWikiSite/';
//     jQuery.post(
//         url,
//         {
//             userId:userId,
//             fromTime:fromTime,
//             toTime:toTime,
//         },
//         function(data){
//             //	console.log(data);
//             if(callback != null){
//                 callback(data);
//             }else{
//                 return data;
//             }
//         }
//     ).error(function(){
//             //console.log("error");
//             var errInfo = {'status':'fail'};
//             if(callback != null) {
//                 callback(errInfo);
//             }else{
//                 return errInfo;
//             }
//         });
// }

// function getEditorCountGroupByWikiSite(fromTime,toTime,callback){
//     var url = 'http://test.huiji.wiki:50007/getEditorCountGroupByWikiSite/';
//     jQuery.post(
//         url,
//         {
//             fromTime:fromTime,
//             toTime:toTime,
//         },
//         function(data){
//             //	console.log(data);
//             if(callback != null) {
//                 callback(data);
//             }else{
//                 return data;
//             }
//         }
//     ).error(function(){
//             //console.log("error");
//             var result = {'status':'fail'};
//             if(callback != null) {
//                 callback(result);
//             }else{
//                 return result;
//             }
//         });
// }

// function getEditRecordsOnWikiSiteFromUserIdGroupByDay(userId,wikiSite,fromTime,toTime,callback)
// {
//     var url = 'http://test.huiji.wiki:50007/getEditRecordsOnWikiSiteFromUserIdGroupByDay/';
//     jQuery.post(
//         url,
//         {
//             userId:userId,
//             wikiSite:wikiSite,
//             fromTime:fromTime,
//             toTime:toTime,
//         },
//         function(data){
//             //	console.log(data);
//             if(callback != null) {
//                 callback(data);
//             }else{
//                 return data;
//             }
//         }
//     ).error(function(){
//             //console.log("error");
//             var result = {'status':'fail'};
//             if(callback != null){
//                 callback();
//             }else{
//                 return result;
//             }
//         });

// }



// function getViewRecordsOnWikiSiteFromUserIdGroupByDay(userId,wikiSite,fromTime,toTime,callback)
// {
//     var url = 'http://test.huiji.wiki:50007/getViewRecordsOnWikiSiteFromUserIdGroupByDay/';
//     jQuery.post(
//         url,
//         {
//             userId:userId,
//             wikiSite:wikiSite,
//             fromTime:fromTime,
//             toTime:toTime
//         },
//         function(data){
// //			console.log(data);
//             if(callback != null) {
//                 callback(data);
//             }else{
//                 data
//                 return data;
//             }
//         }
//     ).error(function(){
//             //console.log("error");
//             var result = {'status':'fail'};
//             if(callback != null){
//                 callback();
//             }else{
//                 return result;
//             }
//         });

// }


// //new pv
// function getPreviousViewRecords(wikiSite,dayNumber,cb)
// {
//         var now = moment();
//         var date_array = new Array();
//         var number_array = new Array();

//         for(var i=1;i<=dayNumber;i++){
//                 date_array[i-1] = moment().subtract(dayNumber-i+1,"days").format("YYYY-MM-DD");
//         }


//         var callback = function(data){

//                 var newData = {};
//                 if(data.result != null){
//                         var hashtable = new Array();
//                         for(var i=0;i<data.result.length;i++){
//                                 hashtable[data.result[i]._id] = data.result[i].value;
//                         }
//                         var rs = {};
//                         rs.date_array = date_array;
//                         for(var i=0;i<date_array.length;i++){
//                                 if(hashtable[date_array[i]]== null){
//                                         number_array[i] = 0;
//                                 }else{
//                                         number_array[i] = hashtable[date_array[i]];
//                                 }
//                         }
//                         rs.number_array = number_array;
//                         newData.result = rs;
//                         newData.status = data.status;
//                 }
//                 cb(newData);
//         }


//         getViewRecordsOnWikiSiteFromUserIdGroupByDay(-1,wikiSite,moment().subtract(1+dayNumber,"days").format("YYYY-MM-DD"),moment().subtract(1,"days").format("YYYY-MM-DD"),callback);



// }

// //new pe
// function getPreviousEditRecords(wikiSite,dayNumber,cb)
// {
//         var now = moment();
//         var date_array = new Array();
//         var number_array = new Array();

//         for(var i=1;i<=dayNumber;i++){
//                 date_array[i-1] = moment().subtract(dayNumber-i+1,"days").format("YYYY-MM-DD");
//         }


//         var callback = function(data){

//                 var newData = {};
//                 if(data.result != null){
//                         var hashtable = new Array();
//                         for(var i=0;i<data.result.length;i++){
//                                 hashtable[data.result[i]._id] = data.result[i].value;
//                         }
//                         var rs = {};
//                         rs.date_array = date_array;
//                         for(var i=0;i<date_array.length;i++){
//                                 if(hashtable[date_array[i]]== null){
//                                         number_array[i] = 0;
//                                 }else{
//                                         number_array[i] = hashtable[date_array[i]];
//                                 }
//                         }
//                         rs.number_array = number_array;
//                         newData.result = rs;
//                         newData.status = data.status;
//                 }
//                 cb(newData);
//         }


//         getEditRecordsOnWikiSiteFromUserIdGroupByDay(-1,wikiSite,moment().subtract(1+dayNumber,"days").format("YYYY-MM-DD"),moment().subtract(1,"days").format("YYYY-MM-DD"),callback);



// }


jQuery( document ).ready( function() {

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
                                magicType : {show: true, type: ['line', 'bar']},
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
                //all pv
                var site = mw.config.get('wgHuijiPrefix');
                // var site = 'lotr';
                getPreviousViewRecords(site,30,updateData);
                function updateData(data){
                     if (data.status == 'success'){
                        // console.log(data.result);
                        // var res = data.result;
                        option.xAxis[0].data=data.result.date_array;
                        option.series[2].data=data.result.number_array;
                        myChart.setOption(option,false);
                    }
                    // alert(mw.config.get('wgHuijiPrefix'));
                }
                //all pe
                getPreviousEditRecords(site,30,updateDatape);
                function updateDatape(data){
                     if (data.status == 'success'){
                        // console.log(data.result);
                        // var res = data.result;
                        // option.xAxis[0].data=data.result.date_array;
                        option.series[3].data=data.result.number_array;
                        myChart.setOption(option,false);
                    }
                    // alert(mw.config.get('wgHuijiPrefix'));
                }
                
                
                // getViewRecordsOnWikiSiteFromUserIdGroupByDay('-1','','','',updateData);
                // function updateData(data){
                //     if (data.status == 'success'){
                //         console.log(data.result);
                //         var res = data.result;
                //         var receive = new Array();
                //         res.forEach(function(res){
                //             var id = res._id;
                //             receive[id] = res.value;
                //         });
                //         // for (var i = 0; i <= res.length; i++) {
                //         //     var key = res[i]._id;
                //         //     receive[key] = res[i].value;
                //         // };
                //         console.log(receive);
                //         // for(var i=0;i<=30;i++){
                           
                //         //     option.series[2].data[i]=res[i].value;
                //         // }

                //         function formatDate(now){     
                //             var   year=now.getFullYear();     
                //             var   month=now.getMonth()+1;     
                //             var   date=now.getDate();       
                //             return   year+"-"+month+"-"+date;     
                //         }     
                        
                //         var dataArr = new Array();
                //         for(var j=0;j<31;j++){
                //             var dd = new Date();
                //             var t=dd.getTime() - 24*60*60*1000*j;
                //             var d=new Date(t);
                //             var day= formatDate(d);
                //             var k=30-j;
                //             option.xAxis[0].data[k]=day;
                //             option.series[0].data[k]=receive[k];
                //         }
                //         // console.log(dataArr);
                //         myChart.setOption(option,false);
                //         // console.log(option.xAxis[0].data);
                //         // console.log(option.series[0].data);
                //     }
                // }
            }
        );
});
