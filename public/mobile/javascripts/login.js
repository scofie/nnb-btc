$(document).keydown(function (event) {
    if (event.keyCode == 13) {
        logon();
    }
});
var vue = new Vue({
    el: '#app',
    data: {
        langIndex:0,
        eye:0,
        userName: '',
        passwords: '',
        checkboxValue: true,
        areaList:[],
        codeId:'',
        areaCode:'',
        areaText:'',
        langBool:false,
        langtxt:[{"txt":getlg('zwjt'),'lang':"zh"},{"txt":getlg('zwft'),"lang":"hk"},{"txt":getlg('yy'),"lang":"en"},{"txt":getlg('hy'),"lang":"kor"},{"txt":getlg('ry'),"lang":"jp"},{"txt":getlg('ty'),"lang":"th"}],
        listShow:false,
        status:'mobile'
    },
    mounted: function () {
        this.langtxt
        for(let i=0;i<this.langtxt.length;i++){
            if(getLocal('language')==this.langtxt[i].lang){
                this.langIndex=i;
                break;
            }    
        }
        
        // if(getLocal('language')=="zh"){
        //     this.langIndex=0;
        // }
        // if(getLocal('language')=="cht"){
        //     this.langIndex=1;
        // }
        // if(getLocal('language')=="en"){
        //     this.langIndex=3;
        // }
        // if(getLocal('language')=="jp"){
        //     this.langIndex=4;
        // }
        // if(getLocal('language')=="kor"){
        //     this.langIndex=5;
        // }
        // if(getLocal('language')=="th"){
        //     this.langIndex=6;
        // }
        let that = this;
        // FastClick.attach(document.body);
        that.userName = localStorage.getItem('userName') || '';
        that.passwords = localStorage.getItem('passwords') || '';
        that.checkboxValue = localStorage.getItem('loginStute') || false;
        // if (that.userName != '' && that.passwords != '') {
        //     $("#sendLogin").css("background", "#588bf7");
        // } else {
        //     $("#sendLogin").css("background", "#7d818a")
        // }
        $.ajax({
            type: "post",
            url: _API + "area_code",
            dataType: "json",
            success: function (data) {
                if (data.type == "ok") {
                    var datas = data.message;
                    if (datas.length > 0) {
                        that.areaList = datas;
                        that.areaText = datas[0].name;
                        that.areaCode = datas[0].area_code;
                        that.codeId = datas[0].id;
                    }
                    

                } else {
                    layer_msg(data.message);
                }
            }
        });
    },
    methods: {
        openlang(){
              this.langBool=true;
        },
        tabLang(lang){
            setLocal('language', lang);
            setLang(lang)
            location.reload();
        },
        langClick(txt){
            if(txt==0){
                txt="zh";
            }
             if(txt==1){
                txt="hk";
            }
            if(txt==2){
                txt="en";
            }
           if(txt==3){
                txt="jp";
            }
           if(txt==4){
                txt="kor"
            }
            if(txt==5){
                txt="th"
            }
            setLocal('language', txt);
            setLang(txt);
            location.reload();
        },
        checkEye(i){
            this.eye=i;
        },
        // 密码显示或者隐藏
        shpass() {
            $("#text").toggle();
            $("#password").toggle();
            if ($("#imgs").attr('src') == 'images/accountm.png') {
                $("#imgs").attr('src', 'images/eyes.png');
            } else {
                $("#imgs").attr('src', 'images/accountm.png');
            }
        },
        passblur() {
            $("#text").val($("#password").val());
        },

        // 密码验证
        passwordConfirm() {
            var pass = $("#password").val();
            // if ($('#name').val() != '' && $('#password').val() != '') {
            //     $("#sendLogin").css("background", "#588bf7");
            // } else {
            //     $("#sendLogin").css("background", "#7d818a")
            // }
            if (pass.length < 6 || pass.length > 16) {
                $("#mes2").html(getlg('plength'));
            } else {
                $("#mes2").html("");
            }
        },
        // 用户验证
        userConfirm() {
            // if ($('#name').val() != '' && $('#password').val() != '') {
            //     $("#sendLogin").css("background", "#588bf7");
            // } else {
            //     $("#sendLogin").css("background", "#7d818a")
            // }
        },
        // 点击登录
        logon() {
            let that = this;
            var reg = /^[0-9]\d*$/;
            if(reg.test(that.userName)){
                that.status="mobile";
            }else if(that.userName.indexOf('@')!=-1){
                that.status="email";
            }else{
                layer_msg(getlg('phoneandemail'));
            }
            if (!that.passwords) {
                layer_msg(getlg('pinpwd'));
                return false;
            } else if (that.passwords.length < 6) {
                layer_msg(getlg('ptpwd'));
                return false;
            }
            if (that.checkboxValue) {
                localStorage.setItem('userName', that.userName);
                localStorage.setItem('passwords', that.passwords);
                localStorage.setItem('loginStute', that.checkboxValue);
            } else {
                localStorage.setItem('userName', '');
                localStorage.setItem('passwords', '');
                localStorage.setItem('loginStute', false);
            }
            var data = {};
            data.user_string = that.userName;
            data.password = that.passwords;
            if(that.status=="mobile"){
                data.area_code = that.areaCode;
            data.area_code_id = that.codeId;
            }
            initDatas({
                url: 'user/login',
                data: data,
                type: 'post'
            }, function (res) {
                if (res.type == 'ok') {
                    layer_msg(getlg('lgsuccess'))
                    set_user(res.message, 7);
                    setTimeout(function () {
                        window.location.href = "index.html";
                    }, 500)
                } else {
                    layer_msg(data.message);

                }
            });
        },
        selectInput(val) {
            let that = this;
        },
        selectedTab(){
            var that = this;
            that.listShow = true;
        },
        areaSelected(ids,areaCode,name){
            var that = this;
            that.codeId = ids;
            that.areaCode = areaCode;
            that.areaText = name;
            that.listShow = false;
        },
        slectedTap(){
            var that = this;
            that.status = that.status=='mobile'?'email':'mobile'
        }


    }
});