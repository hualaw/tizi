define("module/common/basics/common/showLogin",[],function(require,exports){exports.show_login=function(){$.tiziDialog({content:json.msg,icon:"question",ok:function(){window.location.href=baseUrlName+"login"},cancel:!0})}});