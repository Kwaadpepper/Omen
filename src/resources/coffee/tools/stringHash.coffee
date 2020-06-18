module.exports = (string)->
    hash = 0
    i = 0
    while i < string.length
        char = string.charCodeAt(i++)
        hash = ((hash<<5)-hash)+char
        hash = hash & hash # Convert to 32bit integer
    return hash