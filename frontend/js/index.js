import '../styles/layout.scss';

( function( wp, fsData ) {
	wp.fsLikes = {
		init() {
			this.nonce = fsData.nonce;
			this.action = fsData.action;
			this.likeBlocks = document.querySelectorAll( '.fs-likes[data-post-id]' );
			this.makeEvents();
		},
		makeEvents() {
			this.likeBlocks.forEach( ( likeBlock ) => {
				const postId = likeBlock.getAttribute( 'data-post-id' );
				const upvoteButton = likeBlock.querySelector( '.upvote' );
				const downvoteButton = likeBlock.querySelector( '.downvote' );

				upvoteButton.addEventListener( 'click', ( e ) => {
					e.preventDefault();
					upvoteButton.classList.add( 'inactive' );
					this.sendVote( postId, upvoteButton.value );
				} );

				downvoteButton.addEventListener( 'click', ( e ) => {
					e.preventDefault();
					downvoteButton.classList.add( 'inactive' );
					this.sendVote( postId, downvoteButton.value );
				} );
			} );
		},
		sendVote( postId, value ) {
			wp.ajax.post( this.action, {
				post_id: postId,
				_ajax_nonce: this.nonce,
				value,
			} ).done( ( response ) => {
				this.processResponse( response );
			} ).fail( ( error ) => {
				// eslint-disable-next-line no-console
				console.error( error );
			} );
		},
		processResponse( response ) {
			const block = this.getBlockByPostId( response.post_id );
			if ( block instanceof HTMLElement ) {
				block.querySelector( '.fs-likes-count' ).textContent = response.votes_total;
				block.querySelector( '.fs-likes-button.upvote' ).disabled = response.value === 1;
				block.querySelector( '.fs-likes-button.downvote' ).disabled = response.value === -1;
				block.querySelectorAll( '.fs-likes-button' ).forEach( ( button ) => {
					button.classList.remove( 'inactive' );
				} );
			}
		},
		getBlockByPostId( postId ) {
			return [ ...this.likeBlocks ].find( ( block ) => {
				return parseInt( block.getAttribute( 'data-post-id' ) ) === postId;
			} );
		},
	};
	wp.domReady( () => {
		wp.fsLikes.init();
	} );
}( window.wp, window.fsData ) );
