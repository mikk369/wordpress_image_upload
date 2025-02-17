import { useState } from 'react';
import axios from 'axios';
import './App.css';

const handleImageUpload = async (e: any, setImage: React.Dispatch<React.SetStateAction<any>>, setUploading: React.Dispatch<React.SetStateAction<boolean>>) => {
  e.preventDefault();

  const file = e.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);

  setUploading(true);

  try {
    // Make the API request to upload the image
    const response = await axios.post("https://lohvik.ee/wp-json/image/v1/upload_image", formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    console.log(response);

    // Get the URL of the uploaded image and set it in the state
    setImage(response.data.image_url);
  } catch (error) {
    console.error('Error uploading image:', error);
  } finally {
    setUploading(false);
  }
};

function ImageUpload() {
  const [image, setImage] = useState(null);
  const [uploading, setUploading] = useState(false);

  return (
    <div className="upload-image-container">
      <h1>Lae üles menüü</h1>
      <input type="file" onChange={(e) => handleImageUpload(e, setImage, setUploading)} />
      {uploading && <p>Laeb...</p>}

      {image && (
        <div className='image-container'>
          <h3>Üles laetud menüü:</h3>
          <img src={image} alt="Uploaded image"/>
        </div>
      )}
    </div>
  );
}

export default ImageUpload;
