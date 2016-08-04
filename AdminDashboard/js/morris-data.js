jQuery( document ).ready( function() {
    var p1 = $.getScript('http://echarts.baidu.com/build/dist/echarts-all.js');
    $.when(p1).done(function(){
        var  myChart = echarts.init(document.getElementById('morris-area-echart'));
        var  option = {
            tooltip : {
                trigger: 'axis'
            },
            legend: {
                data:['网站得分']
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
                }
            ],
            series : [
                {
                    name:'网站得分',
                    type:'line',
                    // stack: '总量',
                    // color:'red',
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
                    myChart.setOption(option,false);
                }
            }
        );


        var  myChart2 = echarts.init(document.getElementById('morris-area-echart2'));
        var  option = {
            tooltip : {
                trigger: 'axis'
            },
            legend: {
                data:['关注人数']
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
                }
            ],
            series : [
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
                    // markLine:{
                    //     data:[
                    //         {type:'average',name:'平均值'}
                    //     ]
                    // }
                }
            ]
        };


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
                    myChart2.setOption(option,false);
                }
            }
        );

        // 基于准备好的dom，初始化echarts图表
        var  myChart3 = echarts.init(document.getElementById('morris-area-echart3'));
        var  option = {
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:['浏览次数']
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
                    }
                ],
                series : [
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
                
                //all pv
        var site = mw.config.get('wgHuijiPrefix');
        huiji.getPreviousViewRecords(site,30,updateData);
        function updateData(data){
             if (data.status == 'success'){
                option.xAxis[0].data=data.result.date_array;
                option.series[0].data=data.result.number_array;
                myChart3.setOption(option,false);
            }
        }


        var  myChart4 = echarts.init(document.getElementById('morris-area-echart4'));
        var  option = {
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:['编辑次数']
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
                    }
                ],
                series : [
                    
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
                
            var site = mw.config.get('wgHuijiPrefix');
            //all pe
            huiji.getPreviousEditRecords(site,30,updateDatape);
            function updateDatape(data){
                 if (data.status == 'success'){
                    var res = data.result;
                    option.xAxis[0].data=data.result.date_array;
                    option.series[0].data=data.result.number_array;
                    myChart4.setOption(option,false);
                }
            }  

    });
 
    // $.getScript('http://echarts.baidu.com/build/dist/echarts.js', function() {
    //     mw.loader.using('skins.bootstrapmediawiki.huiji.getrecordsinterface.js', function(){
         
    //     });

    // });

});
