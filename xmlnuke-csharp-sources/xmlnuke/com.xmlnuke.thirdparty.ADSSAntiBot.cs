using System;
using System.Data;
using System.Configuration;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;
using System.Drawing;
using System.Drawing.Drawing2D;
using com.xmlnuke.engine;
using com.xmlnuke.international;

namespace com.xmlnuke.thirdparty
{
	public class ADSSAntiBot
	{
		protected Random rnd;
		protected Context _context;

		public static string SESSION_CAPTCHA = "SESSION_IMAGEVALIDATE";
		public string question = "????";

		const int default_width = 250;
		const int default_height = 60;
		const int default_question_height = 25;

		protected Bitmap result = null;

		public int Width;
		public int Height;

		public ADSSAntiBot()
		{
			InitBitmap(default_width, default_height);
			rnd = new Random();
		}

		public ADSSAntiBot(int width, int height)
		{
			InitBitmap(width, height);
			rnd = new Random();
		}

		public ADSSAntiBot(Context context, int chars, bool useChallengeQuestion)
		{
			InitBitmap(default_width, default_height);
			rnd = new Random();

			if (chars < 5)
			{
				chars = 5;
			}

			LanguageCollection mywords = LanguageFactory.GetLanguageCollection(context, LanguageFileTypes.OBJECT, "captcha");

			string[] letters = 
				new string[] { 
					 "AEIOU", 
					 "BCDFGHKLMNPQRSTVXZ" 
				};

			string[] avail = new string[] { "", "" };
			string text = "";

			for (int i = 0; i < chars; i++)
			{
				int iav = rnd.Next(0, 2);
				char letter = letters[iav][rnd.Next(0, letters[iav].Length)];
				avail[iav] += letter;
				text += letter;
			}

			int opt = 2;
			if (useChallengeQuestion)
			{
				opt = rnd.Next(0, 3);
			}
			string result = (opt == 2 ? text : avail[opt]);
			this.question = (opt == 2 ?
				mywords.Value("SIMPLEQUESTION") : (opt == 0 ? 
					mywords.Value("CHALLENGEQUESTION", mywords.Value("VOWEL") ) : 
					mywords.Value("CHALLENGEQUESTION", mywords.Value("CONSONANT") )
					)
				);

			context.setSession(ADSSAntiBot.SESSION_CAPTCHA, result);

			this.DrawText(text);
		}

		protected void InitBitmap(int width, int height)
		{
			result = new Bitmap(width, height + default_question_height);
			Width = width;
			Height = height;
			rnd = new Random();
		}



		protected PointF Noise(PointF p, double eps)
		{
			p.X = Convert.ToSingle(rnd.NextDouble() * eps * 2 - eps) + p.X;
			p.Y = Convert.ToSingle(rnd.NextDouble() * eps * 2 - eps) + p.Y;
			return p;
		}

		protected PointF Wave(PointF p, double amp, double size)
		{
			p.Y = Convert.ToSingle(Math.Sin(p.X / size) * amp) + p.Y;
			p.X = Convert.ToSingle(Math.Sin(p.X / size) * amp) + p.X;
			return p;
		}



		protected GraphicsPath RandomWarp(GraphicsPath path)
		{
			// Add line //
			int PsCount = 10;
			PointF[] curvePs = new PointF[PsCount * 2];
			for (int u = 0; u < PsCount; u++)
			{
				curvePs[u].X = u * (Width / PsCount);
				curvePs[u].Y = Height / 2;
			}
			for (int u = PsCount; u < (PsCount * 2); u++)
			{
				curvePs[u].X = (u - PsCount) * (Width / PsCount);
				curvePs[u].Y = Height / 2 + 2;
			}

			path.AddLines(curvePs);

			//
			double eps = Height * 0.05;

			double amp = rnd.NextDouble() * (double)(Height / 3);
			double size = rnd.NextDouble() * (double)(Width / 4) + Width / 8;

			double offset = (double)(Height / 3);


			PointF[] pn = new PointF[path.PointCount];
			byte[] pt = new byte[path.PointCount];

			GraphicsPath np2 = new GraphicsPath();

			GraphicsPathIterator iter = new GraphicsPathIterator(path);
			for (int i = 0; i < iter.SubpathCount; i++)
			{
				GraphicsPath sp = new GraphicsPath();
				bool closed;
				iter.NextSubpath(sp, out closed);

				Matrix m = new Matrix();
				m.RotateAt(Convert.ToSingle(rnd.NextDouble() * 30 - 15), sp.PathPoints[0]);

				//m.Shear(Convert.ToSingle( rnd.NextDouble()*offset-offset ),Convert.ToSingle( rnd.NextDouble()*offset-offset/2 ));
				//m.Shear(1,1);

				//m.Scale(0.5f + Convert.ToSingle(rnd.NextDouble()), 0.5f + Convert.ToSingle(rnd.NextDouble()), MatrixOrder.Prepend);

				m.Translate(-1 * i, 0);

				sp.Transform(m);

				np2.AddPath(sp, true);
			}




			for (int i = 0; i < np2.PointCount; i++)
			{
				//pn[i] = Noise( path.PathPoints[i] , eps);
				pn[i] = Wave(np2.PathPoints[i], amp, size);
				pt[i] = np2.PathTypes[i];
			}

			GraphicsPath newpath = new GraphicsPath(pn, pt);

			return newpath;

		}

		public static bool TextIsValid(Context context, string text)
		{
			string lastCaptcha = context.getSession(ADSSAntiBot.SESSION_CAPTCHA);
			context.setSession(ADSSAntiBot.SESSION_CAPTCHA, null);
			return (text.ToLower() == lastCaptcha.ToLower());
		}

		public string DrawNumbers(int len)
		{
			string str = "";
			for (int i = 0; i < len; i++)
			{
				int n = rnd.Next() % 10;
				str += n.ToString();
			}
			DrawText(str);
			return str;
		}

		public void DrawText(string aText)
		{
			string fontName = "Arial";

			Graphics g = Graphics.FromImage(result);
			int startsize = Height;
			Font f = new Font(fontName, startsize, FontStyle.Bold, GraphicsUnit.Pixel);

			do
			{
				f = new Font(fontName, startsize, GraphicsUnit.Pixel);
				startsize--;
			} while ((g.MeasureString(aText, f).Width >= Width) || (g.MeasureString(aText, f).Height >= Height));
			SizeF sf = g.MeasureString(aText, f);
			int width = Convert.ToInt32(sf.Width);
			int height = Convert.ToInt32(sf.Height);

			int x = Convert.ToInt32(Math.Abs((double)width - (double)Width) * rnd.NextDouble());
			int y = Convert.ToInt32(Math.Abs((double)height - (double)Height) * rnd.NextDouble());

			//////// Paths ///
			GraphicsPath path = new GraphicsPath(FillMode.Alternate);

			FontFamily family = new FontFamily(fontName);
			int fontStyle = (int)(FontStyle.Regular);
			float emSize = f.Size;
			Point origin = new Point(x, y);
			StringFormat format = StringFormat.GenericDefault;

			path.AddString(aText, family, fontStyle, emSize, origin, format);

			path = RandomWarp(path);
			/// Path ///

			g.TextRenderingHint = System.Drawing.Text.TextRenderingHint.AntiAliasGridFit;
			Rectangle rect = new Rectangle(0, 0, Width, Height);
			g.FillRectangle(new System.Drawing.Drawing2D.LinearGradientBrush(rect, Color.White, Color.LightGray, 0f), rect);
			//g.DrawString(aText, f, new SolidBrush(Color.Black), x, y);
			g.SmoothingMode = SmoothingMode.AntiAlias;
			g.FillPath(new SolidBrush(Color.Black), path);


			Font oFont = new Font(fontName, 13, FontStyle.Regular, GraphicsUnit.Pixel);
			SolidBrush oBrush = new SolidBrush(Color.Black);
			SolidBrush oBrushWrite = new SolidBrush(Color.Black);
			g.FillRegion(new SolidBrush(Color.White), new Region(new Rectangle(0, Height + 1, Width, Height + default_question_height)));
			g.DrawString(this.question, oFont, oBrushWrite, 5, Height + 5);


			// Dispose //
			g.Dispose();
		}

		public Bitmap Result
		{
			get
			{
				return result;
			}
		}
	}
}
