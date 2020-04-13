$.fn.innerText = (msg)->
      if msg
         if document.body.innerText
            for i in this
               this[i].innerText = msg
         else
            for i in this
               this[i].innerHTML.replace(/&amp;lt;br&amp;gt;/gi,"n").replace(/(&amp;lt;([^&amp;gt;]+)&amp;gt;)/gi, "")
            
         return this
      else
         if document.body.innerText
            return this[0].innerText
         else
            return this[0].innerHTML.replace(/&amp;lt;br&amp;gt;/gi,"n").replace(/(&amp;lt;([^&amp;gt;]+)&amp;gt;)/gi, "")
