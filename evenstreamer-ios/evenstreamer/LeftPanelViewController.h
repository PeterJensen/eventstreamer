//
//  LeftPanelViewController.h
//  SlideoutNavigation
//
//  Created by Tammy Coron on 1/10/13.
//  Copyright (c) 2013 Tammy L Coron. All rights reserved.
//

#import <UIKit/UIKit.h>

@protocol LeftPanelViewControllerDelegate <NSObject>

@optional

@required
- (void) eventPicked:(NSString *)eventName;
@end

@interface LeftPanelViewController : UIViewController

@property (nonatomic, assign) id<LeftPanelViewControllerDelegate> delegate;

@end