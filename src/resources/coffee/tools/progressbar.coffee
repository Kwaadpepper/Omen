ProgressBar = require('progressbar.js')
easing = require('bezier-easing')(0.02,0.41,0.61,0.18)

module.exports = (->
    store = {
        progress: 0,
        timeout: null,
        line: null,
        container:  $('body > div.container-fluid')[1] ,
        options: {
            strokeWidth: 0.3,
            easing: 'easeInOut',
            duration: 1400,
            color: '#FFEA82',
            trailColor: '#eee',
            trailWidth: 1,
            svgStyle: {position: 'absolute', top: 0},
            from: {color: '#FFEA82'},
            to: {color: '#ED6A5A'},
            step: (state, bar) -> bar.path.setAttribute('stroke', state.color)
        }
    }
    this.run = ((progress = 0)->
        self = this
        if !self.line then self.line = new ProgressBar.Line(self.container, self.options)
        self.line.set(0)
        self.line.animate(progress)
        $(self.line.svg).show()
        self.progress = progress
        timeoutFunc = ->
            self.progress += 0.06
            if self.progress < 1
                self.line.animate Math.round(easing(self.progress) * 100) / 100
                self.timeout = setTimeout timeoutFunc , 500
            return
        self.timeout = setTimeout timeoutFunc, 500
    ).bind(store)
    this.end = (->
        self = this
        clearTimeout self.timeout
        $(self.line.svg).hide()
        self.line.set(1.0)
        self.line.stop()
        self.progress = 1
    ).bind(store)
    return this
)()