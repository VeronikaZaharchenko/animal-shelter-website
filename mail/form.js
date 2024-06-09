function mail(event){
event.preventDefault()
let formdata= new FormData(document.getElementById('form'))
let options={
    body: formdata,
    method: 'post'   
}

// let url='https://dp-zaharchenko.xn--80ahdri7a.site/mail/mail.php';
let url='mail.php';

fetch(url, options)
    .then((response)=>response.text())
    .then ((response)=>{
        console.log(response)
        let title=document.getElementById('exampleModalLabel')
        let body=document.getElementById('modal-body')

        if (response == 'true'){
            title.innerText = 'Сообщение успешно отправлено';
            body.innerText = 'Наши волонтеры свяжутся с Вами в самое ближайшее время, через электронную почту, которую Вы указали.';
        } else if (response == 'false'){
            title.innerText = 'При отправки сообщения произошла ошибка';
            body.innerText = 'Повторите попытку позже.';
        } else {
            title.innerText = 'Упс!';
            body.innerText = response;
        }
        let myModalAlternative = new bootstrap.Modal('#exampleModal');
        myModalAlternative.show();
    });

}
