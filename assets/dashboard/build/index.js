(()=>{"use strict";var e={n:t=>{var a=t&&t.__esModule?()=>t.default:()=>t;return e.d(a,{a}),a},d:(t,a)=>{for(var r in a)e.o(a,r)&&!e.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:a[r]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.wp.element,a=window.wp.apiFetch;var r=e.n(a);const n=()=>{const[e,a]=(0,t.useState)(!1),[n,s]=(0,t.useState)(null),[l,c]=(0,t.useState)({username:"",preset:"default",cacheDuration:43200,postsPerPage:10}),[o,i]=(0,t.useState)({});return(0,t.useEffect)((()=>{(async()=>{try{const e=await(async()=>{try{return await r()({path:"/nhrcc-core-contributions/v1/settings",method:"GET"})}catch(e){throw console.error("Error fetching settings:",e),e}})();c({...e,cacheDuration:Number(e.cacheDuration)||43200,postsPerPage:Number(e.postsPerPage)||10})}catch(e){s({type:"error",message:"Error loading settings"})}})()}),[]),(0,t.createElement)("div",{className:"nhrcc-core-contributions-settings-wrap"},(0,t.createElement)("h1",{className:"wp-heading-inline"},"Core Contributions Settings"),n&&(0,t.createElement)("div",{className:`notice notice-${"success"===n.type?"success":"error"} is-dismissible`},(0,t.createElement)("p",null,n.message),(0,t.createElement)("button",{type:"button",className:"notice-dismiss",onClick:()=>s(null)},(0,t.createElement)("span",{className:"screen-reader-text"},"Dismiss this notice"))),(0,t.createElement)("div",{className:"card"},(0,t.createElement)("h2",null,"User Settings"),(0,t.createElement)("p",{className:"description"},"Configure the default user and display preferences"),(0,t.createElement)("table",{className:"form-table"},(0,t.createElement)("tbody",null,(0,t.createElement)("tr",null,(0,t.createElement)("th",{scope:"row"},(0,t.createElement)("label",{htmlFor:"username"},"Default WordPress.org Username")),(0,t.createElement)("td",null,(0,t.createElement)("input",{type:"text",id:"username",className:"regular-text",value:l.username,onChange:e=>c({...l,username:e.target.value})}),o.username&&(0,t.createElement)("p",{className:"description error"},o.username),(0,t.createElement)("p",{className:"description"},"This username will be used when no specific user is provided")))))),(0,t.createElement)("div",{className:"card"},(0,t.createElement)("h2",null,"Display Settings"),(0,t.createElement)("p",{className:"description"},"Customize how contributions are displayed"),(0,t.createElement)("table",{className:"form-table"},(0,t.createElement)("tbody",null,(0,t.createElement)("tr",null,(0,t.createElement)("th",{scope:"row"},(0,t.createElement)("label",{htmlFor:"preset"},"Preset")),(0,t.createElement)("td",null,(0,t.createElement)("select",{id:"preset",value:l.preset,onChange:e=>c({...l,preset:e.target.value})},(0,t.createElement)("option",{value:"default"},"Default"),(0,t.createElement)("option",{value:"minimal"},"Minimal")))),(0,t.createElement)("tr",{className:"hidden"},(0,t.createElement)("th",{scope:"row"},(0,t.createElement)("label",{htmlFor:"postsPerPage"},"Posts Per Page")),(0,t.createElement)("td",null,(0,t.createElement)("input",{type:"number",id:"postsPerPage",className:"small-text",value:l.postsPerPage,onChange:e=>c({...l,postsPerPage:Number(e.target.value)})}),o.postsPerPage&&(0,t.createElement)("p",{className:"description error"},o.postsPerPage)))))),(0,t.createElement)("div",{className:"card"},(0,t.createElement)("h2",null,"Cache Settings"),(0,t.createElement)("p",{className:"description"},"Manage how long contribution data is stored"),(0,t.createElement)("table",{className:"form-table"},(0,t.createElement)("tbody",null,(0,t.createElement)("tr",null,(0,t.createElement)("th",{scope:"row"},(0,t.createElement)("label",{htmlFor:"cacheDuration"},"Cache Duration")),(0,t.createElement)("td",null,(0,t.createElement)("select",{id:"cacheDuration",value:l.cacheDuration,onChange:e=>c({...l,cacheDuration:Number(e.target.value)})},(0,t.createElement)("option",{value:"1800"},"30 Minutes"),(0,t.createElement)("option",{value:"3600"},"1 Hour"),(0,t.createElement)("option",{value:"21600"},"6 Hours"),(0,t.createElement)("option",{value:"43200"},"12 Hours"),(0,t.createElement)("option",{value:"86400"},"24 Hours"))))))),(0,t.createElement)("p",{className:"submit"},(0,t.createElement)("button",{type:"button",className:"button button-primary",onClick:async()=>{if((()=>{const e={};return l.username.trim()||(e.username="Username is required"),i(e),0===Object.keys(e).length})()){a(!0);try{const e=await(async e=>{try{return await r()({path:"/nhrcc-core-contributions/v1/settings",method:"POST",data:e})}catch(e){throw console.error("Error updating settings:",e),e}})(l);s({type:"success",message:e.message})}catch(e){s({type:"error",message:"Error saving settings. Please try again later."})}finally{a(!1)}}},disabled:e},e?"Saving...":"Save Changes")))},s=()=>{const e=document.getElementById("nhrcc-admin-settings");e&&(0,t.createRoot)(e).render((0,t.createElement)(n))};"loading"===document.readyState?document.addEventListener("DOMContentLoaded",s):s()})();