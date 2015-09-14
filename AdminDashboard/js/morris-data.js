jQuery( document ).ready( function() {
    $.getScript('http://echarts.baidu.com/build/dist/echarts.js', function() {
        mw.loader.using('skin.bootstrapmediawiki.huiji.getrecordsinterface.js', function(){
         // 路径配置
            // console.log(huiji);
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
                    huiji.getPreviousViewRecords(site,30,updateData);
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
                    huiji.getPreviousEditRecords(site,30,updateDatape);
                    function updateDatape(data){
                         if (data.status == 'success'){
                            // console.log(data.result);
                            var res = data.result;
                            option.xAxis[0].data=data.result.date_array;
                            option.series[3].data=data.result.number_array;
                            console.log(data.result.number_array.length);
                            myChart.setOption(option,false);
                        }
                        // alert(mw.config.get('wgHuijiPrefix'));
                    }

                }
            );
        });

    });

});
