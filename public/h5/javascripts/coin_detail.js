var vue = new Vue({
	el: '#app',
	data: {
	    sideColumnTime:null,
	    select_tab:false,
		List: [],
		coinDealIndex:0,
		currencyRateIndex:'',
		currencyRate:[
			{
				title:"25%",
				num:0.25,
			},
			{
				title:"50%",
				num:0.50,
			},
			{
				title:"75%",
				num:0.75,
			},
			{
				title:"100%",
				num:1,
			},
		],
		name: '',
		id: '',
		twoNum: '1',
		has_address_num: '',
		leftData: [],
		multList: [],
		shareList: [],
		completeList: [],
		sellOut: [],
		buyOut: [],
		currencyId: get_param('id2') || '',
		legalId: get_param('id1') || '',
		more: '加载更多',
		nodataShow: false,
		symbols: get_param('symbol'),
		token: get_user_login(),
		leftShow: false,
		type: getlocal_storage('levertype') || 'buy',
		leverDatas: {
			resMsg: '',
			legalName: '',
			currencyName: '',
			legalId: '',
			currencyId: '',
			cny: '',
			ustdPrice: '',
			shareNum: '',
			transactionFee: '',
			spread: '',
			bond: '',
			tip: '',
			setValue: '',
			selectStatus:0,
			minShare: '',
			maxShare: '',
			marketValue: '',
			bondTotal: '',
			transactionTotal: '',
			lastPrice: '',
			useLegalNum: '',
			useLegalFee:'',
			usebibiFee:'',
			usebibiNum: '',
			useCurrencyNum: '',
			share: '',
			controlPrice: '',
			bestPrice:'',
			resultPrice:0,
			currencyNum:'',
			muitNum: '',
			rmbRate: '',
		},
		modalShow: false,
		positionList: [],
		positionsData: {
			balance: '',
			hazardRate: '',
			profitsTotal: '',

		},
		page: 1,
		forms: {
			prices: '',
			ylValue: '',
			prices1: '',
			ksValue: ''
		},
		htmlStatus: localStorage.getItem('htmlStatus') || '',
		testValue: '最小交易量',
		sumitBtnStatus: false,
		zhiyaList:[],
        weituoList:[],
	},
	filters: {
		toFixeds: function (value) {
			var vals = iTofixed(value,2)
			return vals
		},
		toFixed4: function (value) {
			var vals = iTofixed(value,4)
			return vals
		},
		toFixed5: function (value) {
			var vals = iTofixed(value,5)
			return vals

		},
		toFixed8: function (value) {
			var vals = iTofixed(value,8)
			return vals

		}
	},
	mounted: function () {
		// FastClick.attach(document.body);
		let that = this;
		var leverData = getlocal_storage('lever');
		that.leverDatas.currencyId = get_param('id2') || '';
		that.leverDatas.currencyName = get_param('name2') || '';
		that.leverDatas.legalId = get_param('id1') || '';
		that.leverDatas.legalName = get_param('name1') || '';
		if (get_param('type') && get_param('type') == 2) {
			that.type = 'sell'
		} else {
			that.type = 'buy'
		}
		that.init();
		that.testValue = '最小交易量' + that.leverDatas.minShare;
	
		initDataTokens({
		    url:"wallet/detail",
		    type:"post",
		    data:{
		        currency:that.leverDatas.legalId,
		        type:"change"
		    },
		},function(res){
		    that.leverDatas.useLegalFee=res.message.coin_trade_fee;
		    that.leverDatas.useLegalNum=res.message.change_balance;
		})
		
		initDataTokens({
		    url:"wallet/detail",
		    type:"post",
		    data:{
		        currency:that.leverDatas.currencyId,
		        type:"change"
		    },
		},function(res){
		    that.leverDatas.usebibiFee=res.message.coin_trade_fee;
		    that.leverDatas.usebibiNum=res.message.change_balance;
		    
		})
		initDataTokens({
		    url:"coin/list",
		    data:{
		        currency_id:that.leverDatas.currencyId,
		        legal_id:that.leverDatas.legal_id,
		    }
		},function(res){
		    let data=res.message;
		    for(let i=0;i<data.length;i++){
		        data[i]['symbol_one']=data[i].symbol.split("/")[0];
		        data[i]['symbol_two']=data[i].symbol.split("/")[1];
		    }
		    that.weituoList=data;
		})
	},
	methods: {
	    closeOrder(id,index){
	        let that=this;
	        initDataTokens({
    		    url:"coin/trade",
    		    type:"PUT",
    		    data:{
    		        id:id,
    		    }
    		},function(res){
    		    if(res.type=="ok"){
    		        that.weituoList[index].status=3;
    		    }
    		    layer_msg(res.message);
    		})
	    },
		coinDeal(index){
			this.coinDealIndex=index;	
		},
		currencyNumInput(){
			let that=this;
			if(that.leverDatas.selectStatus==0){
				if(this.leverDatas.controlPrice){
					this.leverDatas.resultPrice=this.leverDatas.controlPrice*this.leverDatas.currencyNum;
				}else{
				    this.leverDatas.currencyNum='';
				}
			}else{
			    if(this.leverDatas.bestPrice){
				this.leverDatas.resultPrice=this.leverDatas.bestPrice*this.leverDatas.currencyNum;
			    }else{
			        this.leverDatas.currencyNum='';
			    }
			}
			
		},
		currencyRateTab(index){
			let that=this;
			if(that.type=="buy"){
			    if(that.leverDatas.useLegalNum<=0.001){
    				layer.msg('可用余额必须大于等于0.001');
    				return;
    			}
			}else{
			    if(that.leverDatas.usebibiNum<0.001){
    				layer.msg('可用余额必须大于等于0.001');
    				return;
    			}
			}
			
			if(that.leverDatas.controlPrice=='' && that.leverDatas.selectStatus==0){
				layer.msg('请输入价格');
				return;
			}
			if(that.leverDatas.selectStatus==1 && that.leverDatas.bestPrice==''){
				return;
			}
			let user_legal_num=that.leverDatas.useLegalNum*that.currencyRate[index].num;
			let usebibiNum=that.leverDatas.usebibiNum*that.currencyRate[index].num;
			if(that.leverDatas.selectStatus==0 && that.leverDatas.controlPrice!=''){
				if(that.type=='buy'){
					that.leverDatas.currencyNum=(user_legal_num/that.leverDatas.controlPrice).toFixed(8);
					that.leverDatas.resultPrice=(that.leverDatas.currencyNum*that.leverDatas.controlPrice).toFixed(8)
				}else{
					that.leverDatas.currencyNum=usebibiNum;
					that.leverDatas.resultPrice=(usebibiNum*that.leverDatas.controlPrice).toFixed(8)
				}
			}
			if(that.leverDatas.selectStatus==1 && that.leverDatas.bestPrice!=''){
				if(that.type=="buy"){
					that.leverDatas.currencyNum=(user_legal_num/that.leverDatas.bestPrice).toFixed(8);
					that.leverDatas.resultPrice=(that.leverDatas.currencyNum*that.leverDatas.bestPrice).toFixed(8);
				}else{
					that.leverDatas.currencyNum=usebibiNum;
					that.leverDatas.resultPrice=(usebibiNum*that.leverDatas.bestPrice).toFixed(8)
				}
			}
			that.currencyRateIndex=index;
		},
		//借还
		borrow(){
			layer.open({
				type: 1,
				title: false,
				area: ['100%', 'auto'],
				skin: 'confirm-modal btn-borrow',
				shadeClose: true,
				fixed: true,
				offset: 't',
				closeBtn: 0,
				// scrollbar: false,
				tipsMore: true,
				content: $('.borrow'),
				shadeClose: true,
				btn: [getlg('huazhuan'), getlg('jiebi'), getlg('haibi')],
				btn1: function (index) {
					window.location.href="quantify_turn.html";
				},
				btn2: function (index) {
					window.location.href="quantify_borrow.html?type=2"
				},
				btn3: function (index) {
					window.location.href="quantify_borrow_detail.html"
				},
				success: function () {

				}
			})
		},
		getselectedVal(ev){
			this.selectMult($("#select option:selected").attr('value'));
		},
		init() {
			var that = this;
			//法币、币种数据请求（左侧内容）
			initDataTokens({
				url: 'currency/quotation_new'
			}, function (res) {
				if (res.type == 'ok') {
					if (res.message.length > 0) {
						that.leftData = res.message;
						let datas = res.message;
						var index1 = 0;
						var index2 = 0;
						var leverData = getlocal_storage('lever');
						for (var i = 0; i < datas.length; i++) {
							if (that.legalId == datas[i].id) {
								index1 = i;
							}
							for (var j = 0; j < datas[i].quotation.length; j++) {
								if (that.currencyId == datas[i].quotation[j].currency_id) {
									index2 = j;
								}
							}
						}
						// 初始化法币、币种渲染
						if (get_param('id1')) {
							that.leverDatas.shareNum = iTofixed(datas[index1].quotation[index2].lever_share_num,2);
							that.leverDatas.spread = datas[index1].quotation[index2].spread;
							that.leverDatas.transactionFee = datas[index1].quotation[index2].lever_trade_fee;
							// $('.share-num').text(share_num);
						} else {
							if (leverData) {
								that.leverDatas.currencyId = leverData.currency_id;
								that.leverDatas.currencyName = leverData.currency_name;
								that.leverDatas.legalId = leverData.legal_id;
								that.leverDatas.legalName = leverData.legal_name;
								if (!leverData.share_num) {
									that.leverDatas.shareNum = iTofixed(datas[index1].quotation[index2].lever_share_num,2);
								} else {
									that.leverDatas.shareNum = leverData.share_num;
								}
								if (!leverData.spread) {
									that.leverDatas.spread = datas[index1].quotation[index2].spread;;
								} else {
									that.leverDatas.spread = leverData.spread;
								}
								if (!leverData.transactionFee) {
									that.leverDatas.transactionFee = datas[index1].quotation[index2].transactionFee;
								} else {
									that.leverDatas.transactionFee = leverData.transactionFee;
								}


								// $('.share-num').text(share_num);
							} else {
								that.leverDatas.currencyId = datas[index1].quotation[index2].currency_id;
								that.leverDatas.currencyName = datas[index1].quotation[index2].currency_name;
								that.leverDatas.legalId = datas[index1].quotation[index2].legal_id;
								that.leverDatas.legalName = datas[index1].quotation[index2].legal_name;
								that.leverDatas.shareNum = iTofixed(datas[index1].quotation[index2].lever_share_num,2);
								that.leverDatas.spread = datas[index1].quotation[index2].spread;
								that.leverDatas.transactionFee = datas[index1].quotation[index2].transactionFee;
							}
						}
						that.leverDatas.rmbRate = datas[index1].quotation[index2].rmb_relation;
						that.testValue = '最小交易量' + that.leverDatas.minShare;
						//交易数据请求
						that.get_lever_data();
						that.scoket();
						that.upPrice();
						that.marsketLists();
					}
				}
			});
		},
		get_lever_data() {
			let that = this;
			if (that.type == 'sell') {
				setTimeout(() => {
					$('.sell').trigger('click')
				}, 50);
				$('.num span:nth-child(2)').add('active');
			} else {
				$('.num span:nth-child(2)').add('actives');
				setTimeout(() => {
					$('.buy').trigger('click')
				}, 50);

			}
// 			initDataTokens({
// 				url: 'lever/deal',
// 				data: {
// 					legal_id: that.leverDatas.legalId,
// 					currency_id: that.leverDatas.currencyId
// 				},
// 				type: 'post'
// 			}, function (res) {
// 				console.log(res);
// 				that.leverDatas.marketValue = 0.000;
// 				that.leverDatas.bondTotal = 0.000;
// 				that.leverDatas.transactionTotal = 0.000;
// 				if (res.type == "ok") {
// 					that.leverDatas.tip = res.message.lever_burst_hazard_rate;
// 					that.multList = res.message.multiple.muit
// 					that.leverDatas.minShare = res.message.lever_share_limit.min;
// 					that.leverDatas.maxShare = res.message.lever_share_limit.max;
// 					that.shareList = res.message.multiple.share;
// 					that.completeList = res.message.my_transaction;
// 					that.testValue = '最小交易量' + that.leverDatas.minShare;
// 					var sellList = res.message.lever_transaction.out.reverse();
// 					var arr1 = [];
// 					var arr = [];
// 					for (i in sellList) {
// 						arr = [];
// 						arr[0] = sellList[i].price;
// 						arr[1] = sellList[i].number;
// 						arr1.push(arr);
// 					}
// 					var buyList = res.message.lever_transaction.in;
// 					var arr2 = [];
// 					var arr3 = [];
// 					for (let i in buyList) {
// 						arr3 = [];
// 						arr3[0] = buyList[i].price;
// 						arr3[1] = buyList[i].number;
// 						arr2.push(arr3);
// 					}
// 					that.buyOut = arr2;
// 					that.sellOut = arr1;
// 					that.leverDatas.cny = res.message.ExRAte - 0 || 1;
// 					that.leverDatas.ustdPrice = res.message.ustd_price;
// 					that.leverDatas.lastprice = res.message.last_price;
// 					that.leverDatas.useLegalNum = res.message.user_lever;
// 					that.leverDatas.useCurrencyNum = res.message.all_levers;
// 					that.leverDatas.muitNum = that.multList[0].value;
// 				} else {
// 					layer_msg(res.message)
// 				}
// 			});
		},
		leftShows() {
			let that = this;
			$('body').css('overflow', 'hidden');
			$('#Limited').hide();
			$('#mask1').show();
			let num=80;
			clearInterval(that.sideColumnTime);
		    that.sideColumnTime=setInterval(function(){
			    num--;
			    $('#sideColumn').css('left', '-'+num+'%');
			    if(num<=0){
			        clearInterval(that.sideColumnTime);
			    }
			},10)
		},
		closeLeft() {
			$('body').css('overflow', 'auto');
			$('#Limited').show();
			$('#mask1').hide();
			$('#sideColumn').css('left', '-80%');
			clearInterval(that.sideColumnTime);
		},
		//socket连接封装
		scoket() {
			let that = this;
			$.ajax({
				url: _API + "user/info",
				type: "GET",
				dataType: "json",
				async: true,
				beforeSend: function beforeSend(request) {
					request.setRequestHeader("Authorization", that.token);
				},
				success: function success(data) {
					if (data.type == 'ok') {
						var socket = io(socket_api);
						socket.emit('login', data.message.id);
						// 后端推送来消息时
						socket.on('market_depth', function (msg) {
							if (msg.type == 'market_depth') {
								if (that.leverDatas.legalId == msg.legal_id && that.leverDatas.currencyId == msg.currency_id) {
									// var buyIn = JSON.parse(msg.bids);
									// var out = JSON.parse(msg.asks).reverse();
									var buyIn = msg.bids;
									var out = msg.asks;
									that.sellOut = out;
									that.buyOut = buyIn;
									var buyOutTotal=0;
									var sellOutTotal=0;
								for(let i=0;i<buyIn.length;i++){
								   buyOutTotal+=buyIn[i][1];
								}
								for(let i=0;i<out.length;i++){
								    sellOutTotal+=out[i][1];
								}
								for(let i=0;i<buyIn.length;i++){
								    
								    buyIn[i]['bg']=buyIn[i][1]/buyOutTotal*100+8;
								}
								for(let i=0;i<out.length;i++){
								    sellOutTotal+=out[i][1];
								     out[i]['bg']=out[i][1]/sellOutTotal*100+8;
								}
								if(that.type=="buy"){
									if(that.leverDatas.bestPrice==""){
										that.leverDatas.bestPrice=that.sellOut[that.sellOut.length-1][0].toFixed(4);
									}
									if(that.leverDatas.controlPrice==""){
										that.leverDatas.controlPrice=that.sellOut[that.sellOut.length-1][0].toFixed(4);
									}
								}else{
									if(that.leverDatas.bestPrice==""){
										that.leverDatas.bestPrice=that.buyOut[0][0].toFixed(4);
									}
									if(that.leverDatas.controlPrice==""){
										that.leverDatas.controlPrice=that.buyOut[0][0].toFixed(4);
									}
								}
									// for (var i = 0; i < out.length; i++) {
									// 	that.sellOut[i] = out[i];
									// }
									// for (var i = 0; i < buyIn.length; i++) {
									// 	that.buyOut[i] = buyIn[i];
									// }
								}
							}

						})
					}
				}
			});
		},
		// 买入/卖出
		submitBtn() {
			var that = this;
			var textValue = /^[1-9]*[0-9][0-9]*$/;
			let currencyNum=parseFloat(that.leverDatas.currencyNum);
			if (currencyNum == '') {
				layer_msg(getlg('ptnum'));
				return false;
			}
			if (that.leverDatas.selectStatus == 0) {
				if (that.leverDatas.controlPrice == '') {
					layer_msg(getlg('ptprice'));
					return false;
				}
			}else{
			    if (that.leverDatas.bestPrice == '') {
					layer_msg(getlg('ptprice'));
					return false;
				}
			}
			if(that.type=="buy"){
			    if(that.leverDatas.useLegalNum-that.leverDatas.resultPrice<0){
			       layer_msg(getlg('runningLow')); 
			       return false;
			    }
			}else{
			    if(that.leverDatas.usebibiNum-that.leverDatas.currencyNum<0){
			       layer_msg(getlg('runningLow')); 
			       return false;
			    }
			}
			if (that.leverDatas.legalId != '' && that.leverDatas.currencyId) {
				var data = {
					legal_id: that.leverDatas.legalId,
					currency_id: that.leverDatas.currencyId,
					type: that.type == 'buy' ? 1 : 2,
					amount:currencyNum,
					target_price: that.leverDatas.selectStatus===0?that.leverDatas.controlPrice:that.leverDatas.bestPrice,
				}
			} else {
				layer_msg(getlg('pchange'))
			}
			that.sumitBtnStatus = true;
			// that.modalShow = true;
			// '<ul class="comfirm-modal"><li><p class="name"></p></li><li><p>' + getlg('ttype') + '</p><p class="type"></p></li><li><p>' + getlg('hands') + '</p><p class="share"></p></li><li><p>' + getlg('multiple') + '</p><p class="muit"></p></li><li><p>' + getlg('bond') + '</p><p class="bondPrice"></p></li></ul>'
			layer.open({
				type: 1,
				title: false,
				shadeClose: true,
				area: ['90%', 'auto'],
				skin: 'confirm-modal btn-text',
				content: $('.modal-submit'),
				btn: [getlg('ceil'), getlg('sure')],
				btn2: function (index) {
					initDataTokens({
						url: 'coin/trade',
						type: 'post',
						data: data
					}, function (res) {
						layer_msg('提交成功');
						if (res.type == 'ok') {
						    setTimeout(function(){
						        initDataTokens({
                        		    url:"coin/list",
                        		    data:{
                        		        currency_id:that.leverDatas.currencyId,
                        		        legal_id:that.leverDatas.legal_id,
                        		    }
                        		},function(res){
                        		    let data=res.message;
                        		    for(let i=0;i<data.length;i++){
                        		        data[i]['symbol_one']=data[i].symbol.split("/")[0];
                        		        data[i]['symbol_two']=data[i].symbol.split("/")[1];
                        		    }
                        		    that.weituoList=data;
                        		})
						      //  location.reload();
						    },1500)
						  
				// 			location.href = 'leverList.html';

						}
					})


				},
				success: function () {
				}
			})





		},
		// 平仓
		sellLoss(ids) {
			layer.open({
				type: 1,
				title: false,
				shadeClose: true,
				skin: 'loads-btn btn-text',
				area: ['70%', 'auto'],
				content: getlg('sureClose'),
				btn: [getlg('ceil'), getlg('sure')],
				btn2: function (index) {
					initDataTokens({
						url: 'lever/close',
						type: 'post',
						data: {
							id: ids
						}
					}, function (res) {
						layer_msg(res.message)
						setTimeout(function () {
							window.location.reload();
						}, 1000)

					})
				}
			});
		},
		// 法币切换
		legalTab(legalid) {
			let that = this;
			that.currencyRateIndex='';
			that.leverDatas.legalId = legalid;
			that.leverDatas.bestPrice='';
			that.leverDatas.controlPrice='';
			that.leverDatas.currencyNum="";
			that.leverDatas.share = "";
			initDataTokens({
    		    url:"wallet/detail",
    		    type:"post",
    		    data:{
    		        currency:that.leverDatas.currencyId,
    		        type:"change"
    		    },
    		},function(res){
    		    that.leverDatas.usebibiFee=res.message.coin_trade_fee;
    		    that.leverDatas.usebibiNum=res.message.change_balance;
    		    
    		})
    		initDataTokens({
    		    url:"coin/list",
    		    data:{
    		        currency_id:that.leverDatas.currencyId,
    		        legal_id:that.leverDatas.legal_id,
    		    }
    		},function(res){
    		    let data=res.message;
    		    for(let i=0;i<data.length;i++){
    		        data[i]['symbol_one']=data[i].symbol.split("/")[0];
    		        data[i]['symbol_two']=data[i].symbol.split("/")[1];
    		    }
    		    that.weituoList=data;
    		})
			$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
			$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
			$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
		},
		// 币种切换 
		currencyTab(legalName, currencyId, currencyName, shareNum, spread, fee, rmbRate) {
			let that = this;
			that.leverDatas.legalName = legalName;
			that.leverDatas.currencyId = currencyId;
			that.leverDatas.currencyName = currencyName;
			that.leverDatas.shareNum = shareNum;
			that.leverDatas.spread = spread;
			that.leverDatas.transactionFee = fee;
			that.leverDatas.share = "";
			that.leverDatas.rmbRate = rmbRate;
			$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
			$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
			$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
			setlocal_storage('lever', {
				"currency_name": currencyName,
				"currency_id": currencyId,
				"legal_name": legalName,
				"legal_id": that.leverDatas.legalId,
				"share_num": shareNum,
				'spread': spread,
				'transactionFee': fee,
			});
			setlocal_storage('levertype', 'buy');
			that.get_lever_data();
			that.close();

		},
		// 关闭左侧 
		close() {
			$('body').css('overflow', 'auto');
			$('#Limited').show();
			$('#mask1').hide();
			clearInterval(this.sideColumnTime);
			$('#sideColumn').animate({
				left: '-80%'
			}, 1000);
		},
		links() {
			let that = this;
			location.href = 'Entrust.html?legal_id=' + that.leverDatas.legalId + '&currency_id=' + that.leverDatas.currencyId + '&legal_name=' + that.leverDatas.legalName + '&currency_name=' + that.leverDatas.currencyName;
		},
		upPrice() {
			let that = this;
			$.ajax({
				url: _API + "user/info",
				type: "GET",
				dataType: "json",
				async: true,
				beforeSend: function beforeSend(request) {
					request.setRequestHeader("Authorization", that.token);
				},
				success: function success(data) {
					if (data.type == 'ok') {
						var socket = io(socket_api);
						socket.emit('login', data.message.id);
						// 后端推送来消息时
						socket.on('kline', function (msg) {
							if (msg.type == 'kline') {
								var symbols = $('.trade-name').text();
								if (symbols == msg.symbol) {
									that.leverDatas.lastprice = msg.close;
								}
							}

						})
					}
				}
			});
		},
		marsketLists() {
			let that = this;
			$.ajax({
				url: _API + "user/info",
				type: "GET",
				dataType: "json",
				async: true,
				beforeSend: function beforeSend(request) {
					request.setRequestHeader("Authorization", that.token);
				},
				success: function success(data) {
					if (data.type == 'ok') {
						var socket = io(socket_api);
						socket.emit('login', data.message.id);
						// 后端推送来消息时
						socket.on('daymarket', function (msg) {
							if (msg.type == 'daymarket') {
								var list1 = that.leftData;
								for (var i = 0; i < list1.length; i++) {
									var list2 = list1[i].quotation
									for (var j = 0; j < list2.length; j++) {
										if (list2[j].legal_id == msg.legal_id && list2[j].currency_id == msg.currency_id) {
											that.leftData[i].quotation[j].now_price = msg.now_price;
											that.leftData[i].quotation[j].change = msg.change;
										}
									}
								}
							}
						});
					}
				}
			});
		},
		// 合约市值、保证金计算
		calculation(bond, type, share, muit) {
			let that = this;
			layer.load(2)
			var spread = iTofixed(that.leverDatas.spread,4);
			var pricesTotal = 0;
			if (type == 'sell') {
				pricesTotal = iTofixed(bond - spread,4);
			} else {
				pricesTotal = iTofixed(Number(bond) + Number(spread),4);
			}
			var shareNum = iTofixed($('.share-num').text(),4);
			var totalPrice = iTofixed(pricesTotal * share * shareNum,4);
			var bonds = iTofixed((totalPrice - 0) / (muit - 0),4);
			var tradeFree = iTofixed(totalPrice * that.leverDatas.transactionFee / 100,4);
			var marketPrice = iTofixed(totalPrice,4);
			if (marketPrice == 'NaN') {
				$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
			} else {
				$('.market-value').text('≈ ' + marketPrice + " " + that.leverDatas.legalName);
			}
			if (bonds == "NaN") {
				$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
			} else {
				that.leverDatas.bondTotal = bonds;
				$('.bond').text('≈ ' + bonds + " " + that.leverDatas.legalName);
			}
			if (tradeFree == "NaN") {
				$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
			} else {
				that.leverDatas.transactionTotal = tradeFree;
				$('.transaction-fee').text('≈ ' + tradeFree + " " + that.leverDatas.legalName);
			}
			setTimeout(function () {
				layer_close();
			}, 500)
		},
		// 选择倍数
		selectMult(num) {
			let that = this;
			that.leverDatas.muitNum = num;
			if (that.leverDatas.selectStatus == 0) {
				if (that.leverDatas.controlPrice != '') {
					if (that.leverDatas.share != '') {
						var bond = iTofixed(that.leverDatas.controlPrice,4);
						var share = iTofixed(that.leverDatas.share,4);
						var muitNum = iTofixed(that.leverDatas.muitNum,4);
						that.calculation(bond, that.type, share, muitNum);
					} else {
						$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
						$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
						$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
					}
				} else {
					$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
					$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
					$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
				}
			} else {
				if (that.leverDatas.share != '') {
					var bond = iTofixed(that.leverDatas.lastprice,4);
					var share = iTofixed(that.leverDatas.share,4);
					var muitNum = iTofixed(that.leverDatas.muitNum,4);
					that.calculation(bond, that.type, share, muitNum);
				} else {
					$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
					$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
					$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
				}
			}
		},
		// 选择手数
		selectShare(num) {
			let that = this;
			that.leverDatas.share = num;
			if (that.leverDatas.selectStatus == 0) {
				if (that.leverDatas.controlPrice != '') {
					if (that.leverDatas.share != '') {
						var bond = iTofixed(that.leverDatas.controlPrice,4);
						var share = iTofixed(that.leverDatas.share,4);
						var muitNum = iTofixed(that.leverDatas.muitNum,4);
						that.calculation(bond, that.type, share, muitNum);
					} else {
						$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
						$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
						$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
					}
				} else {
					$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
					$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
					$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
				}
			} else {
				if (that.leverDatas.share != '') {
					var bond = iTofixed(that.leverDatas.lastprice,4);
					var share = iTofixed(that.leverDatas.share,4);
					var muitNum = iTofixed(that.leverDatas.muitNum,4);;
					that.calculation(bond, that.type, share, muitNum);
				} else {
					$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
					$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
					$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
				}
			}

		},
		// 输入手数
		inputNum() {
			let that = this;
			var textValue = /^[1-9]*[0-9][0-9]*$/;
			$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
			$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
			$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
			if (!textValue.test(that.leverDatas.share)) {
				layer_msg(getlg('pznum'));
				return false;
			} else if ((that.leverDatas.share - 0) < (that.leverDatas.minShare - 0)) {
				layer_msg(getlg('pnoless') + that.leverDatas.minShare);
				return false;
			} else {
				if (that.leverDatas.maxShare > 0) {
					if ((that.leverDatas.share - 0) > (that.leverDatas.maxShare - 0)) {
						layer_msg(getlg('pnomore') + that.leverDatas.maxShare);
						return false;
					}
				}
			}
			that.selectShare(that.leverDatas.share);
		},
		//点击盘口价格
		ckPrice(price){
			this.leverDatas.controlPrice=price;
			this.inputPrice();
		},
		// 输入价格
		inputPrice() {
			let that = this;
			if (that.leverDatas.controlPrice) {
				that.selectShare(that.leverDatas.share);
			} else {
				$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
				$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
				$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
			}
		},
		// 跳转k线页面
		linkLine() {
			let that = this;
			window.location.href = 'coin_market.html?legal_id=' + that.leverDatas.legalId + '&currency_id=' + that.leverDatas.currencyId + '&symbol=' + $('.trade-name').text();

		},
		recordList() {
			let that = this;
			window.location.href = 'coin_history.html';
		},
        selectShow(){
            this.select_tab=!this.select_tab;
        },
		// 选择交易类型
		selectTrade(types) {
			let that = this;
			 this.select_tab=!this.select_tab;
			that.leverDatas.selectStatus = types;
			$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
			$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
			$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
			that.leverDatas.controlPrice = '';
			that.leverDatas.currencyNum='';
			that.leverDatas.resultPrice='';
			that.leverDatas.share = '';
			if (types == 0) {
				if(that.type=="buy"){
					that.leverDatas.controlPrice=that.sellOut[that.sellOut.length-1][0].toFixed(4);
				}else{
					that.leverDatas.controlPrice=that.buyOut[0][0].toFixed(4);
				}
				$('.control-num').show();
				$('.equal').show();
				$('.control-that').hide();
				$('.select-price span').eq(0).addClass('active').siblings().removeClass('active');
			} else {
				if(that.type=="buy"){
					that.leverDatas.bestPrice=that.sellOut[that.sellOut.length-1][0].toFixed(4);
				}else{
					that.leverDatas.bestPrice=that.buyOut[0][0].toFixed(4);
				}
				$('.control-that').show();
				$('.control-num').hide();
				$('.equal').hide();
				$('.select-price span').eq(1).addClass('active').siblings().removeClass('active');
			}
		},
		// 选择类型
		selectType(types) {
			var that = this;
			that.type = types;
			if(types=="buy"){
				that.leverDatas.controlPrice=that.sellOut[that.sellOut.length-1][0].toFixed(4);
			}else{
				that.leverDatas.controlPrice=that.buyOut[0][0].toFixed(4);
			}
			setlocal_storage('levertype', types);
			$('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
			$('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
			$('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
			that.leverDatas.share = '';
			that.leverDatas.currencyNum='';
			that.leverDatas.resultPrice='';
		},
		// 刷新页面
		reload() {
			location.reload();
		}



	}
});