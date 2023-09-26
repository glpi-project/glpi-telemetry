function captchaTokenValidation(token) {
    fetch('/captcha/validation', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: { "captcha_token" : token},
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('captcha token ok');
            return 'captcha token ok';
        } else {
            console.error('captcha validation failed');
            console.log(data)
        }
    })
    .catch(error => {
        console.log(error);
    })
}