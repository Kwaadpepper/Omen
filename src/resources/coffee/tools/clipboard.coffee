module.exports = {
    action: null,
    items : []
    save: (items, action)->
        this.items = []
        for item,k in items
            this.items.push item
        
        switch action
            when 'copy'
            , 'cut'
                this.action = action
            else 
                this.action = copy
    clear: ->
        this.items = []
    get: ->
        this.items
    getAction: ->
        this.action
}