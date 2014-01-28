//
//  ViewController.m
//  Freegee
//
//  Created by Stephan Lee on 12/12/13.
//  Copyright (c) 2013 Stephan Lee. All rights reserved.
//

#import "SWPanelController.h"
#import "MasterViewController.h"
#import "LeftPanelViewController.h"


#define CENTER_TAG 1
#define LEFT_PANEL_TAG 2
#define RIGHT_PANEL_TAG 3

#define CORNER_RADIUS 4

#define SLIDE_TIMING .25
#define PANEL_WIDTH 60

@interface SWPanelController () <CenterViewControllerDelegate, UIGestureRecognizerDelegate>
@property (nonatomic, strong) UINavigationController *centerViewController;
@property (nonatomic, strong) MasterViewController *masterViewController;
@property (nonatomic, strong) LeftPanelViewController *leftPanelViewController;
@property (nonatomic, assign) BOOL showingLeftPanel;
@property (nonatomic, assign) BOOL showPanel;
@property (nonatomic, assign) BOOL swipePanel;

@property (nonatomic, assign) CGPoint preVelocity;
@property (nonatomic, strong) UIPanGestureRecognizer *panRecognizer;

@end

@implementation SWPanelController

@synthesize managedObjectContext;

- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
  self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
  if (self) {
    // Custom initialization
  }
  return self;
}

- (void)didReceiveMemoryWarning
{
  [super didReceiveMemoryWarning];
  // Dispose of any resources that can be recreated.
}

- (void)rootController:(SWPanelController *)controller didFinishWithSave:(BOOL)save {
  
}

#pragma mark -
#pragma mark View Did Load/Unload

-(void)viewDidLoad {
  [super viewDidLoad];
  [self setupView];
}

- (void)viewDidUnload
{
  [super viewDidUnload];
}

#pragma mark -
#pragma mark View Will/Did Appear

- (void)viewWillAppear:(BOOL)animated
{
  [super viewWillAppear:animated];
  [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(orientationChanged:) name:UIDeviceOrientationDidChangeNotification object:nil];
}

- (void)viewDidAppear:(BOOL)animated
{
	[super viewDidAppear:animated];
}

#pragma mark -
#pragma mark View Will/Did Disappear

- (void)viewWillDisappear:(BOOL)animated
{
	[super viewWillDisappear:animated];
}

- (void)viewDidDisappear:(BOOL)animated
{
	[super viewDidDisappear:animated];
}

#pragma mark -
#pragma mark Setup View

-(void)setupView {
  
  UIStoryboard *storyboard = [UIStoryboard storyboardWithName:@"Main" bundle:nil];
  
  self.centerViewController = (UINavigationController *)[storyboard instantiateViewControllerWithIdentifier:@"NavStart"];
  _masterViewController = (MasterViewController *)self.centerViewController.topViewController;
//  _masterViewController.managedObjectContext = self.managedObjectContext;
  _masterViewController.delegate = self;
  
  [self.centerViewController setViewControllers:@[_masterViewController] animated:NO];
  [self addChildViewController:self.centerViewController];
	self.centerViewController.view.tag = CENTER_TAG;
  _masterViewController.leftButton.tag = 1;
  
	[self.view addSubview:self.centerViewController.view];
	[self addChildViewController:self.centerViewController];
	[_centerViewController didMoveToParentViewController:self];
	
	[self setupGestures];
}

-(void)showCenterViewWithShadow:(BOOL)value withOffset:(double)offset {
	if (value) {
		[_centerViewController.view.layer setCornerRadius:CORNER_RADIUS];
		[_centerViewController.view.layer setShadowColor:[UIColor blackColor].CGColor];
		[_centerViewController.view.layer setShadowOpacity:0.8];
		[_centerViewController.view.layer setShadowOffset:CGSizeMake(offset, offset)];
    
	} else {
		[_centerViewController.view.layer setCornerRadius:0.0f];
		[_centerViewController.view.layer setShadowOffset:CGSizeMake(offset, offset)];
	}
}

-(void)resetMainView {
	// remove left and right views, and reset variables, if needed
	if (_leftPanelViewController != nil) {
		[self.leftPanelViewController.view removeFromSuperview];
		self.leftPanelViewController = nil;
		_masterViewController.leftButton.tag = 1;
		self.showingLeftPanel = NO;
	}
  
	// remove view shadows
	[self showCenterViewWithShadow:NO withOffset:0];
}

-(UIView *)getLeftView {
	// init view if it doesn't already exist
	if (_leftPanelViewController == nil)
	{
		// this is where you define the view for the left panel
		self.leftPanelViewController = [[LeftPanelViewController alloc] init];
		self.leftPanelViewController.view.tag = LEFT_PANEL_TAG;
		self.leftPanelViewController.delegate = _masterViewController;
    
		[self.view addSubview:self.leftPanelViewController.view];
    
		[self addChildViewController:_leftPanelViewController];
		[_leftPanelViewController didMoveToParentViewController:self];
    
		_leftPanelViewController.view.frame = CGRectMake(0, 0, self.view.frame.size.width, self.view.frame.size.height);
	}
  
	self.showingLeftPanel = YES;
  
	// setup view shadows
	[self showCenterViewWithShadow:YES withOffset:-2];
  
	UIView *view = self.leftPanelViewController.view;
	return view;
}

#pragma mark - View change for orientation

- (void)orientationChanged:(NSNotification *)notification {
    UIDeviceOrientation orientation = [[UIDevice currentDevice] orientation];
  
  if (UIDeviceOrientationIsLandscape(orientation) || orientation == UIDeviceOrientationPortraitUpsideDown) {
    if (!self.showingLeftPanel) {
      [self movePanelRight];
    }
    _masterViewController.navigationItem.leftBarButtonItem = nil;
    [self removeGestures];
  } else {
    if (!self.showingLeftPanel) {
      _centerViewController.view.frame = [[UIScreen mainScreen] bounds];
    } else {
      [self movePanelToOriginalPosition];
    }
    _masterViewController.navigationItem.leftBarButtonItem = _masterViewController.leftButton;
    [self setupGestures];
  }
}

#pragma mark - Delegate Actions

-(void)movePanelRight {
  [self movePanelRight:NO];
}

-(void)movePanelRight:(BOOL)fullscreen {
	UIView *childView = [self getLeftView];
	[self.view sendSubviewToBack:childView];
  
  CGRect frame = [[UIScreen mainScreen] bounds];
  UIDeviceOrientation orientation = [[UIDevice currentDevice] orientation];
  
  NSInteger offset;
  if (UIDeviceOrientationIsLandscape(orientation) || orientation == UIDeviceOrientationPortraitUpsideDown) {
    frame.origin.x = 180;
    CGFloat height = frame.size.height;
    frame.size.height = frame.size.width;
    offset = fullscreen ? 0 : 180;
    frame.size.width = height - offset;
  } else {
    offset = fullscreen ? 0 : PANEL_WIDTH;
    frame.origin.x = frame.size.width - offset;
  }
  
  dispatch_async(dispatch_get_main_queue(), ^{

	[UIView animateWithDuration:SLIDE_TIMING
                        delay:0
                      options:UIViewAnimationOptionBeginFromCurrentState animations:^{
                        _centerViewController.view.frame = frame;
                      }
                   completion:^(BOOL finished) {
                     if (finished) {
                       _masterViewController.leftButton.tag = 0;
                     }
                   }
   ];
  });
}

-(void)movePanelToOriginalPosition {
  CGRect frame = [[UIScreen mainScreen] bounds];
  UIDeviceOrientation orientation = [[UIDevice currentDevice] orientation];
  
  if (UIDeviceOrientationIsLandscape(orientation) || orientation == UIDeviceOrientationPortraitUpsideDown) {
    CGFloat height = frame.size.height;
    frame.size.height = frame.size.width;
    frame.size.width = height;
  }
  
  dispatch_async(dispatch_get_main_queue(), ^{
	[UIView animateWithDuration:SLIDE_TIMING
                        delay:0
                      options:UIViewAnimationOptionBeginFromCurrentState
                   animations:^{
                     _centerViewController.view.frame = frame;
                   }
                   completion:^(BOOL finished) {
                     if (finished) {
                       [self resetMainView];
                     }
                   }
   ];
  });
}

-(void)togglePanelRight {
  if (_showingLeftPanel) {
    [self movePanelToOriginalPosition];
  } else {
    [self movePanelRight];
  }
}

#pragma mark -
#pragma mark Swipe Gesture Setup/Actions

#pragma mark - setup

-(void)setupGestures {
  if (self.panRecognizer == nil) {
    self.panRecognizer = [[UIPanGestureRecognizer alloc] initWithTarget:self action:@selector(movePanel:)];
    [self.panRecognizer setMinimumNumberOfTouches:1];
    [self.panRecognizer setMaximumNumberOfTouches:1];
    [self.panRecognizer setDelegate:self];
  }
  
	[_centerViewController.view addGestureRecognizer:self.panRecognizer];
}

-(void)removeGestures {
  [_centerViewController.view removeGestureRecognizer:self.panRecognizer];
}

-(void)movePanel:(id)sender {
	[[[(UITapGestureRecognizer*)sender view] layer] removeAllAnimations];
  
	CGPoint translatedPoint = [(UIPanGestureRecognizer*)sender translationInView:self.view];
	CGPoint velocity = [(UIPanGestureRecognizer*)sender velocityInView:[sender view]];
	CGPoint touchLocation = [(UIPanGestureRecognizer*)sender locationInView:self.view];
  
  
  if (self.centerViewController.topViewController != self.masterViewController) return;
  
	if([(UIPanGestureRecognizer*)sender state] == UIGestureRecognizerStateBegan) {
    UIView *childView = nil;
    
    if(_showingLeftPanel || (touchLocation.x < 40 && velocity.x > 0)) {
      
      childView = [self getLeftView];
      // make sure the view we're working with is front and center
      [self.view sendSubviewToBack:childView];
      [[sender view] bringSubviewToFront:[(UIPanGestureRecognizer*)sender view]];
      _swipePanel = YES;
    } else {
      _swipePanel = NO;
    }
    [(UIPanGestureRecognizer*)sender setTranslation:CGPointMake(0,0) inView:self.view];
	}
  
  if (!_swipePanel) return;
  if([(UIPanGestureRecognizer*)sender state] == UIGestureRecognizerStateEnded) {
    if (!_showPanel) {
      [self movePanelToOriginalPosition];
    } else {
      if (_showingLeftPanel) {
        [self movePanelRight];
      }
    }
	} else if([(UIPanGestureRecognizer*)sender state] == UIGestureRecognizerStateChanged) {
    
    // are we more than halfway, if so, show the panel when done dragging by setting this value to YES (1)
    _showPanel = abs([sender view].center.x - _centerViewController.view.frame.size.width / 2) > _centerViewController.view.frame.size.width / 2;
    
    if ([sender view].center.x + translatedPoint.x - _centerViewController.view.frame.size.width / 2 > 0) {
      [sender view].center = CGPointMake([sender view].center.x + translatedPoint.x, [sender view].center.y);
    } else {
      [sender view].center = CGPointMake(_centerViewController.view.frame.size.width / 2, [sender view].center.y);
    }
    [(UIPanGestureRecognizer*)sender setTranslation:CGPointMake(0,0) inView:self.view];
    
    
    // if you needed to check for a change in direction, you could use this code to do so
    if (velocity.x * _preVelocity.x + velocity.y * _preVelocity.y > 0) {
      // NSLog(@"same direction");
    } else {
      // NSLog(@"opposite direction");
    }
    
    _preVelocity = velocity;
	}
}


@end
