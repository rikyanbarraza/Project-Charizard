(window.webpackWcBlocksJsonp=window.webpackWcBlocksJsonp||[]).push([[19],{172:function(e,t,n){"use strict";var o=n(20),c=n.n(o),r=n(26),l=n.n(r),a=n(32),i=n(7),s=n.n(i),u=(n(206),["className","disabled","name","permalink","rel","style","onClick"]);t.a=function(e){var t=e.className,n=void 0===t?"":t,o=e.disabled,r=void 0!==o&&o,i=e.name,d=e.permalink,p=void 0===d?"":d,b=e.rel,m=e.style,v=(e.onClick,l()(e,u)),f=s()("wc-block-components-product-name",n);if(r){var y=v;return React.createElement("span",c()({className:f},y,{dangerouslySetInnerHTML:{__html:Object(a.decodeEntities)(i)}}))}return React.createElement("a",c()({className:f,href:p,rel:b},v,{dangerouslySetInnerHTML:{__html:Object(a.decodeEntities)(i)},style:m}))}},206:function(e,t){},327:function(e,t,n){"use strict";n.d(t,"a",(function(){return c})),n(219);var o=n(104),c=function(){return o.m>1}},329:function(e,t){},404:function(e,t,n){"use strict";n.r(t);var o=n(154),c=n(5),r=n.n(c),l=n(26),a=n.n(l),i=n(7),s=n.n(i),u=n(84),d=n(218),p=n(327),b=n(172),m=n(52),v=(n(329),["children","headingLevel","elementType"]),f=function(e){var t=e.children,n=e.headingLevel,o=e.elementType,c=void 0===o?"h".concat(n):o,r=a()(e,v);return React.createElement(c,r,t)},y=Object(o.withProductDataContext)((function(e){var t,n,o,c,l,a,i,v=e.className,y=e.headingLevel,O=void 0===y?2:y,g=e.showProductLink,h=void 0===g||g,j=e.align,w=e.textColor,k=e.fontSize,S=e.style,C=Object(u.useInnerBlockLayoutContext)().parentClassName,P=Object(u.useProductDataContext)().product,E=Object(m.a)().dispatchStoreEvent,L=Object(d.getColorClassName)("color",w),z=Object(d.getFontSizeClass)(k),N=s()("wp-block-woocommerce-product-title",(o={"has-text-color":w||(null==S||null===(t=S.color)||void 0===t?void 0:t.text)||(null==S?void 0:S.color)},r()(o,"has-font-size",k||(null==S||null===(n=S.typography)||void 0===n?void 0:n.fontSize)||(null==S?void 0:S.fontSize)),r()(o,L,L),r()(o,z,z),o)),_={fontSize:(null==S?void 0:S.fontSize)||(null==S||null===(c=S.typography)||void 0===c?void 0:c.fontSize),color:(null==S||null===(l=S.color)||void 0===l?void 0:l.text)||(null==S?void 0:S.color)};return P.id?React.createElement(f,{headingLevel:O,className:s()(v,"wc-block-components-product-title",(a={},r()(a,"".concat(C,"__product-title"),C),r()(a,"wc-block-components-product-title--align-".concat(j),j&&Object(p.a)()),a))},React.createElement(b.a,{className:s()(r()({},N,Object(p.a)())),disabled:!h,name:P.name,permalink:P.permalink,rel:h?"nofollow":"",onClick:function(){E("product-view-link",{product:P})},style:Object(p.a)()?_:{}})):React.createElement(f,{headingLevel:O,className:s()(v,"wc-block-components-product-title",(i={},r()(i,"".concat(C,"__product-title"),C),r()(i,"wc-block-components-product-title--align-".concat(j),j&&Object(p.a)()),r()(i,N,Object(p.a)()),i))})}));function O(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(e);t&&(o=o.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,o)}return n}function g(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?O(Object(n),!0).forEach((function(t){r()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):O(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}var h={headingLevel:{type:"number",default:2},showProductLink:{type:"boolean",default:!0},productId:{type:"number",default:0}};Object(p.a)()&&(h=g(g({},h),{},{align:{type:"string"},color:{type:"string"},customColor:{type:"string"},fontSize:{type:"string"},customFontSize:{type:"number"}}));var j=h;t.default=Object(o.withFilteredAttributes)(j)(y)}}]);