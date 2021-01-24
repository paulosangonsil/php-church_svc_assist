// https://gist.github.com/donpandix/f1d638c3a1a908be02d5
function validaDv(T){
    var M=0,S=1;

    for(;T;T=Math.floor(T/10)) {
        S=(S+T%10*(9-M++%6))%11;
    }

    return S?S-1:'k';
}

function validaRut (rutCompleto) {
    if (!/^[0-9]+[-|-]{1}[0-9kK]{1}$/.test( rutCompleto )) {
        return false;
    }

    var tmp = rutCompleto.split('-');
    var digv = tmp[1]; 
    var rut = tmp[0];

    if ( digv == 'K' ) digv = 'k' ;

    return (validaDv(rut) == digv );
}

// https://ui.dev/validate-email-address-javascript/
function validateEmail(mail) {
    var mailformat = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    fnRet = (mail.match(mailformat) != null);

    return fnRet;
}

function formatTelNmb(telNmb) {
    var fRet = telNmb;
    var maxLen = 9;
    var minLen = 8;

    if (telNmb != null) {
        var telLen = telNmb.length;

        telNmb = telNmb.replace(/ /g, "");

        if (telLen > minLen) {
            telLen = telNmb.length;

            if (telLen > maxLen) {
                telNmb = telNmb.substr(telLen - maxLen, maxLen);
            }
        }

        fRet = telNmb;
    }

    return fRet;
}

function validateTel(telNmb) {
    var telformat = /^\d{8,9}$/;

    fnRet = (telNmb.match(telformat) != null);

    return fnRet;
}

function validateName(name, maxSz) {
    let nameformat = new RegExp('^\\D{3,' + maxSz + '}$', 'i');

    fnRet = nameformat.test(name);

    return fnRet;
}