# https://stackoverflow.com/questions/2343343/how-can-i-determine-the-current-line-number-in-javascript
module.exports = ()->
    e = new Error()
    if (!e.stack)
        try
            # IE requires the Error to actually be throw or else the Error's 'stack'
            # property is undefined.
            throw e;
        catch e
            if (!e.stack)
                return 0 # IE < 10, likely
    stack = e.stack.toString().split(/\r\n|\n/)
    #  We want our caller's frame. It's index into |stack| depends on the
    # browser and browser version, so we need to search for the second frame:
    frameRE = /:(\d+):(?:\d+)[^\d]*$/

    loop
        frame = stack.shift()
        unless !frameRE.exec(frame) and stack.length
            break

    return frameRE.exec(stack.shift())[1];