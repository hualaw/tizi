define("module/common/method/common/height",[],function(require,exports){exports.leftMenuBg=function(){var _wrapContentHeight=$("#wrapContent").height(),_slideHeight=$("#slide").height();_wrapContentHeight>_slideHeight?$("#slide").height(_wrapContentHeight):$("#wrapContent").height(_slideHeight)}});