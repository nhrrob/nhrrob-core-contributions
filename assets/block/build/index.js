(()=>{"use strict";const e=window.wp.blocks,t=window.wp.blockEditor,r=window.wp.components,s=window.wp.element,n=JSON.parse('{"UU":"nhrcc-core-contributions/core-contributions-block"}'),o=window.ReactJSXRuntime;(0,e.registerBlockType)(n.UU,{attributes:{username:{type:"string",default:""}},supports:{html:!1,reusable:!0,align:!0},edit:({attributes:e,setAttributes:n})=>{const i=(0,t.useBlockProps)(),[a,c]=(0,s.useState)(e.username),[u,l]=(0,s.useState)(""),[d,p]=(0,s.useState)(!1),h=(0,s.useRef)((()=>{let e;return(...t)=>{clearTimeout(e),e=setTimeout((()=>(e=>{n({username:e})})(...t)),500)}})()).current;return(0,s.useEffect)((()=>{e.username?(p(!0),wp.apiFetch({path:"/nhr/v1/render-shortcode",method:"POST",data:{shortcode:`[nhrcc_core_contributions username="${e.username}"]`}}).then((e=>{l(e.rendered||"")})).catch((()=>{l("Failed to load preview.")})).finally((()=>{p(!1)}))):l("")}),[e.username]),(0,o.jsxs)("div",{...i,children:[(0,o.jsx)(t.InspectorControls,{children:(0,o.jsx)(r.PanelBody,{title:"Settings",children:(0,o.jsx)(r.TextControl,{label:"WordPress.org Username",value:a,onChange:e=>{c(e),h(e)}})})}),d?(0,o.jsx)(r.Spinner,{}):e.username?(0,o.jsx)("div",{className:"nhr-core-contributions-preview",children:(0,o.jsx)("p",{dangerouslySetInnerHTML:{__html:u}})}):(0,o.jsx)("p",{children:"Please set a WordPress.org username to preview the contributions."})]})},save:()=>null})})();