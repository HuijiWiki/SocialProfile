jQuery( document ).ready( function() {
    var p1 = $.getScript('http://echarts.baidu.com/build/dist/echarts-all.js');
    $.when(p1).done(function(){
        var  myChart = echarts.init(document.getElementById('morris-area-echart'));
        var  option = {
            tooltip : {
                trigger: 'axis'
            },
            legend: {
                data:['站点评分']
            },
            toolbox: {
                show : true,
                feature : {
                    mark : {show: false},
                    dataView : {show: true, readOnly: true},
                    magicType : {show: true, type: ['bar', 'line']},
                    // magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                    restore : {show: false},
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
                }
            ],
            color: [
                mw.config.get('wgPrimaryColor')
            ],
            series : [
                {
                    name:'站点评分',
                    type:'bar',
                    // stack: '总量',
                    color:'#333333',
                    // line style
                    // itemStyle:{
                    //     normal:{
                    //         lineStyle:{
                    //             color:'black',
                    //             width:3
                    //         }
                    //     }
                    // },
                    data:[],

                    // 系列中的数据标注内容 series.markPoint  
                    markPoint:{  
                        data:[  
                            {type:'max',name:'最大值'},  
                            {type:'min',name:'最小值'}  
                        ]  
                    },  
                    //系列中的数据标线内容 series.markLine  
                    // markLine:{  
                    //     data:[  
                    //         {type:'average',name:'平均值'}  
                    //     ]  
                    // }  
                }
            ]
        };
        // };
        // 为echarts对象加载数据
        

        //site rank
        jQuery.post(
            mw.util.wikiScript(), {
            action: 'ajax',
            rs: 'wfGetSiteRank',
            rsargs: []
            },
            function( data ) {
                var res = jQuery.parseJSON(data);
                if ( res.success ){
                    option.xAxis[0].data=res.result.date;
                    option.series[0].data=res.result.rank;
                    option.legend.data[0] = "站点评分";
                    option.series[0].name = option.legend.data[0];
                    myChart.setOption(option,false);
                }
            }
        );


        var  myChart2 = echarts.init(document.getElementById('morris-area-echart2'));

        //site follow count
        jQuery.post(
            mw.util.wikiScript(), {
            action: 'ajax',
            rs: 'wfGetSiteFollowedUsers',
            rsargs: []
            },
            function( data ) {
                var res = jQuery.parseJSON(data);
                if ( res.success ){
                    option.xAxis[0].data=res.result.date;
                    option.series[0].data=res.result.FollowCount;
                    option.legend.data[0] = "关注人数";
                    option.series[0].name = option.legend.data[0];
                    myChart2.setOption(option,false);
                }
            }
        );

        // 基于准备好的dom，初始化echarts图表
        var  myChart3 = echarts.init(document.getElementById('morris-area-echart3'));
        var site = mw.config.get('wgHuijiPrefix');
        huiji.getPreviousViewRecords(site,30,updateData);
        function updateData(data){
             if (data.status == 'success'){
                option.xAxis[0].data=data.result.date_array;
                option.series[0].data=data.result.number_array;
                option.legend.data[0] = "浏览次数";
                option.series[0].name = option.legend.data[0];
                myChart3.setOption(option,false);
            }
        }


        var  myChart4 = echarts.init(document.getElementById('morris-area-echart4'));
            //all pe
            huiji.getPreviousEditRecords(site,30,updateDatape);
            function updateDatape(data){
                 if (data.status == 'success'){
                    var res = data.result;
                    option.xAxis[0].data=data.result.date_array;
                    option.series[0].data=data.result.number_array;
                    option.legend.data[0] = "编辑次数";
                    option.series[0].name = option.legend.data[0];
                    myChart4.setOption(option,false);
                }
            }  

    });
 
    // $.getScript('http://echarts.baidu.com/build/dist/echarts.js', function() {
    //     mw.loader.using('skins.bootstrapmediawiki.huiji.getrecordsinterface.js', function(){
         
    //     });

    // });

});
