document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ratingForm');
    const rateButton = document.getElementById('rateButton');
    const ratingSlider = document.getElementById('ratingSlider');
    const ratingMessage = document.getElementById('ratingMessage');
    const toggleRatingButton = document.getElementById('editRatingButton');
    const yourRatingNumber = document.getElementById('yourRatingNumber');
    const yourRatingSuffix = document.getElementById('yourRatingSuffix');
  
    if (toggleRatingButton) {
      toggleRatingButton.addEventListener('click', () => {
        form.style.display = 'flex';
        toggleRatingButton.style.display = 'none';
        ratingSlider.disabled = false;
        rateButton.style.display = 'inline-block';
        yourRatingSuffix.style.display = 'inline';
      });
    }
  
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(form);
  
        fetch('rate.php', { method: 'POST', body: formData })
          .then(res => res.text())
          .then(data => {
            if (data.trim() === 'Rating successfully submitted') {
              const newRating = ratingSlider.value;
  
              yourRatingNumber.textContent = newRating;
              yourRatingSuffix.style.display = 'inline';
  
              rateButton.style.display = 'none';
              form.style.display = 'none';
              toggleRatingButton.style.display = 'inline-block';
              toggleRatingButton.innerText = 'Edit Rating';
  
              ratingSlider.disabled = true;
  
              ratingMessage.style.display = 'block';
              setTimeout(() => {
                ratingMessage.style.opacity = '0';
                setTimeout(() => {
                  ratingMessage.style.display = 'none';
                  ratingMessage.style.opacity = '1';
                }, 500);
              }, 1500);
            } else {
              alert('There was an error submitting your rating.');
            }
          })
          .catch(err => {
            console.error(err);
            alert('Request failed.');
          });
      });
    }
  
    // ===== FAVORITES LOGIC =====
    const favoriteBtn = document.getElementById('favorite-btn');
  
    if (favoriteBtn) {
      favoriteBtn.addEventListener('click', function (e) {
        e.preventDefault(); 
  
        const contentId = favoriteBtn.getAttribute('data-content-id');
        const contentType = favoriteBtn.getAttribute('data-content-type');
        
        console.log(contentId);  
        
        fetch('favorite.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `content_id=${encodeURIComponent(contentId)}&content_type=${encodeURIComponent(contentType)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                favoriteBtn.textContent = 'Added to Favorites';
                favoriteBtn.classList.remove('btn-success');
                favoriteBtn.classList.add('btn-secondary');
                favoriteBtn.disabled = true;
            } else {
                alert(data.error || 'There was an error adding to favorites.');
            }
        })
          .catch(err => {
            console.error(err);
            alert('Request failed.');
          });
      });
    }
  
  });
  