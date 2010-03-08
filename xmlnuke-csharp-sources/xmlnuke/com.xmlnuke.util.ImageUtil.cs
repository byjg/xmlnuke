/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  CSharp Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
 * 
 *  This file is part of XMLNuke project. Visit http://www.xmlnuke.com
 *  for more information.
 *  
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= 
 */

/* http://snippets.dzone.com/posts/show/1485
 * http://www.switchonthecode.com/tutorials/csharp-tutorial-image-editing-saving-cropping-and-resizing
 * http://blog.nineon.com/chintan/post/Resizing-Images-to-match-it-s-Scale-in-C.aspx
 * http://forums.asp.net/t/29645.aspx
 * 
 */

using System;
using System.Drawing;
using System.Drawing.Drawing2D;
using System.Drawing.Imaging;
using System.IO;

namespace com.xmlnuke.util
{
	public enum ImageFlip
	{
		Horizontal, 
		Vertical,
		Both
	}

	public enum StampPosition
	{
		TopRight,
		TopLeft,
		BottomRight,
		BottomLeft,
		Center,
		Top,
		Bottom,
		Left,
		Right,
		Random
	}

	public class ImageUtil
	{
		protected Image _image;

		public ImageUtil(byte[] imageFile) : this(new MemoryStream(imageFile))
		{ }

		public ImageUtil(Stream streamFile)
		{
			this._image = Image.FromStream(streamFile);
		}

		public ImageUtil(Bitmap bitmap)
		{
			Graphics g = Graphics.FromImage((Image)bitmap);
			g.InterpolationMode = InterpolationMode.HighQualityBicubic;

			g.DrawImage(this._image, 0, 0, bitmap.Width, bitmap.Height);
			g.Dispose();
		}

		public ImageUtil(Image image)
		{
			this._image = image;
		}

		public ImageUtil(string fileName)
		{
			this._image = Image.FromFile(fileName);
		}

		public Image GetImage()
		{
			return this._image;
		}

		public void Save(string file)
		{
			string extension = Path.GetExtension(file);

			switch (extension)
			{
				case ".jpg":
				case ".jpeg":
					{
						this._image.Save(file, ImageFormat.Jpeg);
						break;
					}
				case ".gif":
					{
						this._image.Save(file, ImageFormat.Gif);
						break;
					}
				case ".png":
					{
						this._image.Save(file, ImageFormat.Png);
						break;
					}
				case ".ico":
					{
						this._image.Save(file, ImageFormat.Icon);
						break;
					}
				case ".tif":
				case ".tiff":
					{
						this._image.Save(file, ImageFormat.Tiff);
						break;
					}
				default:
					{
						throw new Exception("I cant reconignize this file type by your extension");
					}
			}
		}

	    public int getWidth()
	    {
            return this._image.Width;
	    }

	    public int getHeight()
	    {
            return this._image.Height;
	    }

		/// <summary>
		/// 
		/// </summary>
		/// <see cref=""/>
		/// <param name="targetW"></param>
		/// <param name="targetH"></param>
		/// <returns></returns>
		public ImageUtil resize(int newWidth, int newHeight)
		{
			return this.resize(new Size(newWidth, newHeight), false);
		}

		/// <summary>
		/// Resize and mantain aspect ratio
		/// </summary>
		/// <param name="maxSize"></param>
		/// <returns></returns>
		public ImageUtil resize(int maxSize)
		{
			return this.resize(new Size(maxSize, maxSize), true);
		}

		protected ImageUtil resize(Size size, bool maintainAspectRatio)
		{
			int sourceWidth = this._image.Width;
			int sourceHeight = this._image.Height;

			//float nPercent = 0;
			float nPercentW = 0;
			float nPercentH = 0;

			nPercentW = ((float)size.Width / (float)sourceWidth);
			nPercentH = ((float)size.Height / (float)sourceHeight);

			if (maintainAspectRatio)
			{
				if (nPercentH < nPercentW)
					nPercentW = nPercentH;
				else
					nPercentH = nPercentW;
			}

			int destWidth = (int)(sourceWidth * nPercentW);
			int destHeight = (int)(sourceHeight * nPercentH);

			Bitmap b = new Bitmap(destWidth, destHeight);
			Graphics g = Graphics.FromImage((Image)b);
			g.InterpolationMode = InterpolationMode.HighQualityBicubic;

			g.DrawImage(this._image, 0, 0, destWidth, destHeight);
			g.Dispose();

			this._image = (Image)b;

			return this;
		}

		public ImageUtil cropImage(Rectangle cropArea)
		{
			Bitmap bmpImage = new Bitmap(this._image);
			Bitmap bmpCrop = bmpImage.Clone(cropArea, bmpImage.PixelFormat);
			this._image = (Image)(bmpCrop);

			return this;
		}

		public ImageUtil stampImage(Image imgWatermark, StampPosition position)
		{
			return this.stampImageAndText(imgWatermark, position, null);
		}

		public ImageUtil stampText(string text)
		{
			return this.stampImageAndText(null, StampPosition.Random, text);
		}

		/// <summary>
		/// 
		/// </summary>
		/// <see cref="http://www.codeproject.com/KB/GDI-plus/watermark.aspx"/>
		/// <param name="imgWatermark"></param>
		/// <param name="text"></param>
		/// <returns></returns>
		public ImageUtil stampImageAndText(Image imgWatermark, StampPosition position, string text)
		{
			int phWidth = this._image.Width;
			int phHeight = this._image.Height;

			//create a Bitmap the Size of the original photograph
			Bitmap bmPhoto = new Bitmap(phWidth, phHeight, PixelFormat.Format24bppRgb);

			bmPhoto.SetResolution(this._image.HorizontalResolution, this._image.VerticalResolution);

			//load the Bitmap into a Graphics object 
			Graphics grPhoto = Graphics.FromImage(bmPhoto);

			//Set the rendering quality for this Graphics object
			grPhoto.SmoothingMode = SmoothingMode.AntiAlias;

			//Draws the photo Image object at original size to the graphics object.
			grPhoto.DrawImage(
				this._image,                            // Photo Image object
				new Rectangle(0, 0, phWidth, phHeight), // Rectangle structure
				0,                                      // x-coordinate of the portion of the source image to draw. 
				0,                                      // y-coordinate of the portion of the source image to draw. 
				phWidth,                                // Width of the portion of the source image to draw. 
				phHeight,                               // Height of the portion of the source image to draw. 
				GraphicsUnit.Pixel);                    // Units of measure 

			//------------------------------------------------------------
			//Step #1 - Insert Copyright message
			//------------------------------------------------------------
			if (!string.IsNullOrEmpty(text))
			{
				//-------------------------------------------------------
				//to maximize the size of the Copyright message we will 
				//test multiple Font sizes to determine the largest posible 
				//font we can use for the width of the Photograph
				//define an array of point sizes you would like to consider as possiblities
				//-------------------------------------------------------
				int[] sizes = new int[] { 16, 14, 12, 10, 8, 6, 4 };

				Font crFont = null;
				SizeF crSize = new SizeF();

				//Loop through the defined sizes checking the length of the Copyright string
				//If its length in pixles is less then the image width choose this Font size.
				for (int i = 0; i < 7; i++)
				{
					//set a Font object to Arial (i)pt, Bold
					crFont = new Font("arial", sizes[i], FontStyle.Bold);
					//Measure the Copyright string in this Font
					crSize = grPhoto.MeasureString(text, crFont);

					if ((ushort)crSize.Width < (ushort)phWidth)
						break;
				}

				//Since all photographs will have varying heights, determine a 
				//position 5% from the bottom of the image
				int yPixlesFromBottom = (int)(phHeight * .05);

				//Now that we have a point size use the Copyrights string height 
				//to determine a y-coordinate to draw the string of the photograph
				float yPosFromBottom = ((phHeight - yPixlesFromBottom) - (crSize.Height / 2));

				//Determine its x-coordinate by calculating the center of the width of the image
				float xCenterOfImg = (phWidth / 2);

				//Define the text layout by setting the text alignment to centered
				StringFormat StrFormat = new StringFormat();
				StrFormat.Alignment = StringAlignment.Center;

				//define a Brush which is semi trasparent black (Alpha set to 153)
				SolidBrush semiTransBrush2 = new SolidBrush(Color.FromArgb(153, 0, 0, 0));

				//Draw the Copyright string
				grPhoto.DrawString(text,                 //string of text
					crFont,                                   //font
					semiTransBrush2,                           //Brush
					new PointF(xCenterOfImg + 1, yPosFromBottom + 1),  //Position
					StrFormat);

				//define a Brush which is semi trasparent white (Alpha set to 153)
				SolidBrush semiTransBrush = new SolidBrush(Color.FromArgb(153, 255, 255, 255));

				//Draw the Copyright string a second time to create a shadow effect
				//Make sure to move this text 1 pixel to the right and down 1 pixel
				grPhoto.DrawString(text,                 //string of text
					crFont,                                   //font
					semiTransBrush,                           //Brush
					new PointF(xCenterOfImg, yPosFromBottom),  //Position
					StrFormat);                               //Text alignment
			}


			//------------------------------------------------------------
			//Step #2 - Insert Watermark image
			//------------------------------------------------------------
			if (imgWatermark != null)
			{
				int wmWidth = imgWatermark.Width;
				int wmHeight = imgWatermark.Height;

				//Create a Bitmap based on the previously modified photograph Bitmap
				Bitmap bmWatermark = new Bitmap(bmPhoto);
				bmWatermark.SetResolution(this._image.HorizontalResolution, this._image.VerticalResolution);
				//Load this Bitmap into a new Graphic Object
				Graphics grWatermark = Graphics.FromImage(bmWatermark);

				//To achieve a transulcent watermark we will apply (2) color 
				//manipulations by defineing a ImageAttributes object and 
				//seting (2) of its properties.
				ImageAttributes imageAttributes = new ImageAttributes();

				//The first step in manipulating the watermark image is to replace 
				//the background color with one that is trasparent (Alpha=0, R=0, G=0, B=0)
				//to do this we will use a Colormap and use this to define a RemapTable
				ColorMap colorMap = new ColorMap();

				//My watermark was defined with a background of 100% Green this will
				//be the color we search for and replace with transparency
				colorMap.OldColor = Color.FromArgb(255, 0, 255, 0);
				colorMap.NewColor = Color.FromArgb(0, 0, 0, 0);

				ColorMap[] remapTable = { colorMap };

				imageAttributes.SetRemapTable(remapTable, ColorAdjustType.Bitmap);

				//The second color manipulation is used to change the opacity of the 
				//watermark.  This is done by applying a 5x5 matrix that contains the 
				//coordinates for the RGBA space.  By setting the 3rd row and 3rd column 
				//to 0.3f we achive a level of opacity
				float[][] colorMatrixElements = { 
												new float[] {1.0f,  0.0f,  0.0f,  0.0f, 0.0f},       
												new float[] {0.0f,  1.0f,  0.0f,  0.0f, 0.0f},        
												new float[] {0.0f,  0.0f,  1.0f,  0.0f, 0.0f},        
												new float[] {0.0f,  0.0f,  0.0f,  0.3f, 0.0f},        
												new float[] {0.0f,  0.0f,  0.0f,  0.0f, 1.0f}};
				ColorMatrix wmColorMatrix = new ColorMatrix(colorMatrixElements);

				imageAttributes.SetColorMatrix(wmColorMatrix, ColorMatrixFlag.Default,
					ColorAdjustType.Bitmap);


				int offset = 10;
				int xPosOfWm = 0;
				int yPosOfWm = 0;

				if (position == StampPosition.Random)
				{
					Random rand = new Random();
					position += rand.Next(0, 8);
				}

				switch (position)
				{
					case StampPosition.TopRight:
						{
							xPosOfWm = ((phWidth - wmWidth) - offset);
							yPosOfWm = offset;
							break;
						}
					case StampPosition.TopLeft:
						{
							xPosOfWm = offset;
							yPosOfWm = offset;
							break;
						}
					case StampPosition.BottomRight:
						{
							xPosOfWm = ((phWidth - wmWidth) - offset);
							yPosOfWm = ((phHeight - wmHeight) - offset);
							break;
						}
					case StampPosition.BottomLeft:
						{
							xPosOfWm = offset;
							yPosOfWm = ((phHeight - wmHeight) - offset);
							break;
						}
					case StampPosition.Center:
						{
							xPosOfWm = (phWidth - wmWidth) / 2;
							yPosOfWm = (phHeight - wmHeight) / 2;
							break;
						}
					case StampPosition.Top:
						{
							xPosOfWm = (phWidth - wmWidth) / 2;
							yPosOfWm = offset;
							break;
						}
					case StampPosition.Bottom:
						{
							xPosOfWm = (phWidth - wmWidth) / 2;
							yPosOfWm = ((phHeight - wmHeight) - offset);
							break;
						}
					case StampPosition.Left:
						{
							xPosOfWm = offset;
							yPosOfWm = (phHeight - wmHeight) / 2;
							break;
						}
					case StampPosition.Right:
						{
							xPosOfWm = ((phWidth - wmWidth) - offset);
							yPosOfWm = (phHeight - wmHeight) / 2;
							break;
						}
					default:
						{
							throw new Exception("Something is really bad!");
						}
				}

				grWatermark.DrawImage(imgWatermark,
					new Rectangle(xPosOfWm, yPosOfWm, wmWidth, wmHeight),  //Set the detination Position
					0,                  // x-coordinate of the portion of the source image to draw. 
					0,                  // y-coordinate of the portion of the source image to draw. 
					wmWidth,            // Watermark Width
					wmHeight,		    // Watermark Height
					GraphicsUnit.Pixel, // Unit of measurment
					imageAttributes);   //ImageAttributes Object

				//Replace the original photgraphs bitmap with the new Bitmap
				this._image = bmWatermark;
				grPhoto.Dispose();
				grWatermark.Dispose();
			}
			return this;
		}
	}
}
