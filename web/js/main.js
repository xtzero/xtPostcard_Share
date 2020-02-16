function a(message) {
    alert(message)
}

$(function(){
    $('.bg').addClass('active')
    $('.openMsg').click(function(){
        $('#verifycode').show()
    })
    $('.close-get').click(function(){
        $('#verifycode').hide()
    })
    $('.code-get').click(function(){
        $('.code-get').text('获取中')
        var verifyCode = $('#code-input').val()
        if (!verifyCode) {
            a('请输入取件码')
            $('.code-get').text('取件')
            return
        }

        $.ajax({
            url: 'http://hostname/index.php/readPostcard',
            dataType: "json",
            async: true,
            data: {
                verifycode: verifyCode
            },
            type: "GET",
            success: function(req){
                if (req.code == 200) {
                    console.log(req)
                    $('#content').text(req.data[0].content)
                    $('#from').text('来自：' + req.data[0].from_name)
                    $('#verifycode').hide()
                    $('.message').hide()
                    $('.popBg').show()
                    $('.postcard.cnt').show()
                } else {
                    a(req.msg)
                }
            },
            complete: function(){
                $('.code-get').text('取件')
            },
            error: function(){
                alert('参加人数太多啦，一会再来吧~')
            }
        })
    })
    $('.postcard.cnt .close').click(function(){
        $('.message').show()
        $('.popBg').hide()
        $('.postcard.cnt').hide()
    })
    $('.sendShow').click(function(){
        $('.popBg').show()
        $('.postcard.send').show()
    })
    $('.sendShow1').click(function(){
        $('.postcard.cnt').hide()
        $('.popBg').show()
        $('.postcard.send').show()
    })
    $('.postcard.send .close').click(function(){
        $('.popBg').hide()
        $('.postcard.send').hide()
        $('.message').show()
    })
    $('.sendPhoneTest').click(function() {
        var mobile = $('#sendCard-mobileInput').val()
        if (!mobile) {
            a('请输入手机号')
            return
        }
        if ($('.sendPhoneTest').text() != '发送验证码') {
            return
        }
        sendStatus()
        $.ajax({
            url: 'http://hostname/index.php/sendVeryfyCode',
            dataType: "json",
            async: true,
            data: {
                mobile: mobile
            },
            type: "GET",
            success: function(req){
                if (req.code == 200) {
                    $('.sendPhoneTest').text('已发送')
                    $('#sendCard-verifyCode').show()
                    $('#rec').show()
                    $('#sendPostcard').show()
                    $('#sendPostcard-a').show()
                } else {
                    a(req.msg)
                    normalStatus()
                }
            },
            error: function(){
                alert('参加人数太多啦，一会再来吧~')
            }
        })
        function sendStatus() {
            $('.sendPhoneTest').text('发送中')
            $('.sendPhoneTest').css('background-color', 'gray')
        }
        function normalStatus() {
            $('.sendPhoneTest').text('发送验证码')
            $('.sendPhoneTest').css('background-color', '#f189a2')
        }
    })
    $('#sendPostcard-a').click(function (e) { 
        if ($('#sendPostcard-a').text() == '再发一个') {
            // location.reload()
            $('#sendCard-mobileInput').val('');
            $('#sendCard-verifyCode-input').val('');
            $('#sendTo-input').val('');
            $('#fromname-input').val('');
            $('#sendContent-textarea').val('');
            $('#sendCard-verifyCode').hide()
            $('#rec').hide()
            $('#sendPostcard').hide()
            $('#sendPostcard-a').hide()
            $('.sendPhoneTest').text('发送验证码')
            $('.sendPhoneTest').css('background-color', '#f189a2');
            $('#sendPostcard-a').css('background-color', '#f189a2');
            $('#sendPostcard-a').text('发送')
            return
        }
        if ($('#sendPostcard-a').text() != '发送') {
            return
        }
        var myMobile = $('#sendCard-mobileInput').val();
        if (!myMobile) {
            a('请输入你自己的手机号')
            return
        }
        var verifyCode = $('#sendCard-verifyCode-input').val();
        if (!verifyCode) {
            a('请输入验证码')
            return
        }
        var sendToMobile = $('#sendTo-input').val();
        if (!sendToMobile) {
            a('请输入接受人的手机号')
            return
        }
        var fromName = $('#fromname-input').val();
        if (!fromName) {
            a('请告诉我怎样称呼你，不然对方不知道是谁发的')
            return
        }
        var content = $('#sendContent-textarea').val();
        if (!content) {
            a('你好像没有输入内容哦……寄明信片总要说点什么吧？')
            return
        }
        $('#sendPostcard-a').text('发送中');
        $.ajax({
            url: 'http://hostname/index.php/sendPostcard',
            dataType: "json",
            async: true,
            data: {
                mobile: myMobile,
                verifycode: verifyCode,
                from_name: fromName,
                content: content,
                sendto: sendToMobile
            },
            type: "GET",
            success: function(req){
                if (req.code == 200) {
                    $('#sendPostcard-a').css('background-color', 'gray');
                    a('发送成功')
                    $('#sendPostcard-a').text('再发一个');
                } else {
                    $('#sendPostcard-a').text('发送');
                    a(req.msg)
                }
            },
            error: function(){
                alert('参加人数太多啦，一会再来吧~')
            }
        })
    });

    $('#wemade').click(function (e) { 
        a("这个东西是无聊且不用过情人节的xt做的。\n感谢js兄弟帮我做前端，让所有人能摸到这个页面。js兄弟会找到对象的。\n\n感谢下面朋友们帮我测试：\n楠哥\nhermit小新\nmia姐姐\n华晨宇的鑫浩\n远在一千多公里外的dlb\n坐在我右边的bb\n\n\n2020.02.14")
    });
    textScreen()
    $(window).resize(function(){
        textScreen()
    })
    function textScreen(){
        if($(window).width() < $(window).height() ){
            // var height = $(window).height()
            // $('body').css('width', height + 'px')
            // document.body.style.webkitTransform = 'rotate(90deg)';
            $('body').attr('class','arround')
        } else {
            $('body').attr('class','normal')
        }
    }
})