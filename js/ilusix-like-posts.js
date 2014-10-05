jQuery(document).ready(function() {
    
    jQuery('div.ilpb').on('click', function() {
        
        var curElement     = jQuery(this);
        var curLikeCount   = parseInt(curElement.find('span.ilpb-like-count').html());
        var postId         = 0;
        var likePost       = (curElement.hasClass('no-like')) ? true : false;
        
        jQuery.each(curElement.attr('class').split(' '), function(key, cn) {
            if(cn.indexOf('postid-') > -1) {
                postId = parseInt(cn.substr(cn.indexOf('-') + 1, cn.length));
                return;
            }
        });
        
        jQuery.post(
            ilp_ajaxurl,
            {
                'action': 'ix_like_post',
                'data': {
                    'postid': postId,
                    'likepost': likePost
                }
            }
        );
        
        jQuery(curElement).animate({opacity: 0}, 100, function() {
            if(likePost) {
                curElement.removeClass('no-like').addClass('like');
                curElement.find('span.ilpb-like-text').html(ilp_unlike_text);
                
                if(curLikeCount != undefined) {
                    curLikeCount++;
                    jQuery(curElement.find('span.ilpb-like-count')).html(curLikeCount);
                }
            } else {
                curElement.removeClass('like').addClass('no-like');
                curElement.find('span.ilpb-like-text').html(ilp_like_text);
                
                if(curLikeCount != undefined && curLikeCount > 0) {
                    curLikeCount--;
                    jQuery(curElement.find('span.ilpb-like-count')).html(curLikeCount);
                }
            }
            
            jQuery(curElement).animate({opacity: 1}, 100);
        });
        
    });
    
});