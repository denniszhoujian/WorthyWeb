
/**
 *
 *
 **/

function disposeNumber(value,num_digits){
    if(value == null || value == ""){
        return 0;
    }else if(value.toString().indexOf(".") == -1){
        return value;
    }else{
        return round(value, num_digits);
    }
}
function round(v,e){
    var t=1;
    for(;e>0;t*=10,e--);
    for(;e<0;t/=10,e++);
    return Math.round(v*t)/t;
}